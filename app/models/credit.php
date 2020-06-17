<?php

namespace Models;

use Core\Db;
use Core\Model;

class Credit extends Model {

    const CREDIT_STATUS_PENDING = 'pending';
    const CREDIT_STATUS_GRANTED = 'granted';
    const CREDIT_STATUS_CLOSED = 'closed';

    private $_DATA = [
        'id' => null,
        'user_id' => null,
        'amount' => null,
        'currency' => null,
        'installment' => null,
        'state' => null,
    ];

    public function __construct($id) {
        parent::__construct();
        // Pobranie danych z DB i ustawienie ich w zmiennej $_DATA;
        $q = "SELECT * FROM credit WHERE id = ?";
        $DATA = $this->db->fetchRow($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $id]]);

        if ($DATA) {
            foreach ($DATA as $key => $value) {
                if(array_key_exists($key, $this->_DATA))
                    $this->_DATA[$key] = $value;
            }
        }
    }

    /**
     * Funkcja przyznajaca kredyt uzytkownikowi
     * 
     * @param int $installment
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function acceptCredit($transaction = true) {
        // Przygotowanie zapytania
        if ($transaction) {
            $this->db->begin_transaction();
        }

        $USER = User::getUser($this->_DATA['user_id']);
        $ACCOUNT = Account::getAccountDataByUser($USER['login']);


        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankNumber = $CONFIG['bank']['bank_account'];
        $userAccount = $ACCOUNT['number'];

        $q = "
            UPDATE credit
            SET state = ?
            WHERE id = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => self::CREDIT_STATUS_GRANTED],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $this->_DATA['id']]
        ];

        //Wykonanie zapytania
        if(!$this->db->update($q, $BIND)) {
            // Nie udalo sie zmienic statusu kredytu
            $this->db->rollback();
            $ERRORS[] = "Nie udało się zmienic statusu kredytu";
            return [false, ['credit' => $ERRORS]];
        }

        list($success, $ERRORS) = Transfer::add($bankNumber, $userAccount, 'Kredyt', $this->_DATA['amount'], $USER['login'], 'Udzielenie kredytu', null, false);
        if (!$success) {
            return [false, $ERRORS];
        }

        // Bankowi nie mogą skończyć się pieniądze (drukarka robi brrr)
        $bank = new Account($bankNumber);
        list($success, $ERRORS) = $bank->deposit($this->_DATA['amount'], false);
        if (!$success) {
            return [false, $ERRORS];
        }

        $installment = (int)$this->_DATA['installment'];
        $amount = round($this->_DATA['amount'] / $installment, 2);
        $lastInstallment = $this->_DATA['amount'] - ($amount * ($installment - 1));

        for ($i = 1; $i <= $installment; $i++) {
            $q = "
            INSERT INTO installment
            (credit_id, amount)
            VALUES( ?, ?);
            ";
    
            if ($i == $installment) {
                $BIND = [
                    ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $this->_DATA['id']],
                    ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $lastInstallment]
                ];
            } else {
                $BIND = [
                    ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $this->_DATA['id']],
                    ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $amount]
                ];
            }
    
            //Wykonanie zapytania
            if(!$this->db->insert($q, $BIND)) {
                // Nie udalo sie dodac rat, wycofanie zmian
                $this->db->rollback();
                $ERRORS[] = "Nie udało się dodać raty";
                return [false, ['credit' => $ERRORS]];
            }
        }

        // Udalo sie dodac raty, utrwal zmiany
        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja zamykajaca kredyt
     * 
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function closeCredit($transaction = true) {
        $ERRORS = [];

        // Sprawdzenie czy mozna zamknac kredyt
        $q = "
            SELECT COUNT(*)
            FROM installment
            WHERE credit_id = ?
        ";

        $installments = $this->db->fetchOne($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $this->_DATA['id']]]);

        $q = "
            SELECT COUNT(*)
            FROM installment
            WHERE credit_id = ? AND paid = 1
        ";

        $installmentsPaid = $this->db->fetchOne($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $this->_DATA['id']]]);

        if ($installmentsPaid < $installments) {
            $ERRORS['credit'] = "Nie można zamknąć kredytu, nie został on jeszcze spłacony";
        }

        if (!empty($ERRORS)) {
            return [false, ['credit'] => $ERRORS];
        }

        // Przygotowanie zapytania
        if ($transaction) {
            $this->db->begin_transaction();
        }

        $q = "
            UPDATE credit
            SET state = ?
            WHERE id = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => self::CREDIT_STATUS_CLOSED],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $this->_DATA['id']]
        ];

        //Wykonanie zapytania
        if(!$this->db->update($q, $BIND)) {
            // Nie udalo sie zmienic statusu, wycofanie zmian
            $this->db->rollback();
            $ERRORS[] = "Nie udało się zmienic statusu";
            return [false, ['credit' => $ERRORS]];
        }

        // Udalo sie dodac konto, utrwal zmiany
        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja zamykajaca kredyt
     * 
     * @param int $id
     * @param string $login
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function payInstallment($id, $login, $transaction = true) {
        // Przygotowanie zmiennych do zapytania
        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankAccount = $CONFIG['bank']['bank_account'];
        $ACCOUNT = Account::getAccountDataByUser($login);

        $q = "
            SELECT *
            FROM installment
            WHERE id = ?
        ";

        $INSTALLMENT = $this->db->fetchRow($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $id]]);

        if ($transaction) {
            $this->db->begin_transaction();
        }

        list($success, $ERRORS) = Transfer::add($ACCOUNT['number'], $bankAccount, 'Rata', $INSTALLMENT['amount'], 'JPD', 'Rata kredytu', null, false);
        if(!$success) {
            // Nie udalo sie zaplacic raty, wycofanie zmian
            $this->db->rollback();
            return [false, ['credit' => $ERRORS]];
        }

        $q = "
            UPDATE installment
            SET paid = 1
            WHERE id = ?
        ";

        if (!$this->db->update($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $id]])) {
            // Nie udalo sie zaplacic raty, wycofanie zmian
            $this->db->rollback();
            $ERRORS[] = "Nie udlo sie zmienic statusu raty";
            return [false, ['credit' => $ERRORS]];
        }

        $this->closeCredit(false);

        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja dodaje kredyt do uzytkownika
     * 
     * @param string $login
     * @param double $amount
     * @param string $currency
     * @return array(bool, array())
     */
    public static function addCredit($login, $amount, $currency, $installment) {
        $ERRORS = [];

        // Sprawdzenie poprawnosci zmiennych
        $USER = User::getUsers($login);
        if (empty($USER)) {
            $ERRORS['user'] = "Uzytkownik nie istnieje";
        }

        if (!preg_match('/^(\d{1,10})(\.|,)?(\d{0,2})$/', $amount)) {
            $ERRORS['amount'] = "Należy podać kwotę w postaci: 9999999999 lub 9999999999,99";
        }

        if (!empty($ERRORS)) {
            return [false, ['credit' => $ERRORS]];
        }

        // Przygotowanie zapytania
        $q = "
        INSERT INTO credit
        (user_id, amount, currency, installment, state)
        VALUES(?, ?, ?, ?, ?);
        ";
    
        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $USER['id']],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $amount],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $currency],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $installment],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => self::CREDIT_STATUS_PENDING],
        ];

        // Rozpoczecie transakcji
        $db = Db::getInstance();
        $db->begin_transaction();

        // Dodanie nowego przelewu, w wypadku niepowodzenia wycofanie zmian w DB i zwrocenie bledu
        if (!$db->insert($q, $BIND)) {
            $db->rollback();
            return [false, ['transfer' => $ERRORS]];
        }

        // Utrwalenie zmian w DB i zwrocenie sukcesu
        $db->commit();

        return [true,[]];
    }

    /**
     * Funkcja sluzy do pobrania informacji o kredycie uzytkownika
     * 
     * @param string $login
     * @return array|false
     */
    public static function getCreditsByLogin($login) {
        $q = "
            SELECT c.*
            FROM credit c
            JOIN user u ON u.id = c.user_id
            WHERE u.login = ?
        ";

        return Db::getInstance()->fetchAll($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]]);
    }

    /**
     * Funkcja sluzy do pobrania informacji o kredytach do zaakceptowania
     * 
     * @return array|false
     */
    public static function getPendingCredits() {
        $q = "
            SELECT *, c.id AS id
            FROM credit c
            JOIN user u ON u.id = c.user_id
            WHERE c.state = '" . self::CREDIT_STATUS_PENDING ."'";

        return Db::getInstance()->fetchAll($q);
    }

    /**
     * Funkcja sluzy do pobrania informacji o kredycie uzytkownika z ratami
     * 
     * @param string $login
     * @return array|false
     */
    public static function getCreditWithInstallment($id) {
        $q = "
            SELECT *, c.amount AS credit_amount
            FROM credit c
            JOIN installment i ON i.credit_id = c.id
            WHERE c.id = ?
        ";

        return Db::getInstance()->fetchAll($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $id]]);
    }
}

?>