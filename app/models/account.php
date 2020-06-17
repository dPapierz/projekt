<?php

namespace Models;

use Core\Db;
use Core\Model;

class Account extends Model { 
    private $_DATA = [
        'number' => null,
        'user' => null,
        'balance' => 0,
        'currency' => 'PL',
    ];

    public function __construct($number = null) {
        parent::__construct();
        if (!empty($number)) {
            // Pobranie danych z DB i ustawienie ich w zmiennej $_DATA;
            $q = "SELECT * FROM account WHERE number = ?";
            $DATA = $this->db->fetchRow($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $number]]);

            if ($DATA) {
                foreach ($DATA as $key => $value) {
                    if(array_key_exists($key, $this->_DATA))
                        $this->_DATA[$key] = $value;
                }
            }
        }
    }

    /**
     * Funckcja dodaje nowego uzytkownika do systemu
     * 
     * @param string $login
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function addAccount($login, $transaction = true) {
        $USER = User::getUsers($login);

        // Wygenerowanie nowego numernu konta
        $accountNumber = $iban = null;
        for($i = 0; $i < 10; $i++) {
            list($accountNumber, $iban) = self::generateAccountNumber();
            if(!self::getAccountData($iban))
                break;
        }

        // Sprawdzenie czy wystapily bledy
        if (empty($accountNumber) || empty($iban)) {
            $ERRORS['number'] = "Nie udało się wyznaczyć nowego numeru";
        }

        if (!empty($ERRORS)) {
            return [false, ['account' => $ERRORS]];
        }

        // Przygotowanie zapytania
        if ($transaction) {
            $this->db->begin_transaction();
        }

        $q = "
        INSERT INTO account
        (number, user_id, currency)
        VALUES( ?, ?, 'PLN');
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $iban],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $USER['id']]
        ];

        //Wykonanie zapytania
        if(!$this->db->insert($q, $BIND)) {
            // Nie udalo sie dodac konta, wycofanie zmian
            $this->db->rollback();
            $ERRORS[] = "Nie udało się dodać konta";
            return [false, ['account' => $ERRORS]];
        }

        // Udalo sie dodac konto, utrwal zmiany
        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja zmienia dane w istniejacym koncie
     * 
     * @param double $balance
     * @param double $deposit
     * @param string $currency
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function changeAccount($balance, $deposit, $currency, $transaction = true) {
        $ERRORS = [];

        // Przygotowanie zapytania
        if ($transaction) {
            $this->db->begin_transaction();
        }

        $q = "
        UPDATE account
        SET balance = ?, debit = ?, currency = ?
        WHERE number = ?;
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $balance],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $deposit],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $currency],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['number']]
        ];

        // Wykonanie update
        if(!$this->db->update($q, $BIND)) {
            // Niepowodzenie, wycofanie zmian
            $this->db->rollback();
            $ERRORS[] = "Nie udało się zmienić konta";
            return [false, ['account' => $ERRORS]];
        }

        // Utrwalenie zmian
        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja sluzy do dodania pieniedzy do konta
     * 
     * @param double $amount
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function deposit($amount, $transaction = true) {
        $ERRORS = [];

        // Sprawdzanie poprawnosci operacji
        // jesli podano bledny numer rzucic bledem
        if (!preg_match('/^(\d{1,10})(\.|,)?(\d{0,2})$/', $amount)) {
            $ERRORS['amount'] = "Należy podać kwotę w postaci: 9999999999 lub 9999999999,99";
        }

        // Jesli znaleziono jakis blad zwrocic niepowodzenie z bledem
        if (!empty($ERRORS)) {
            return [false, $ERRORS];
        }

        // Rozpoczac transakcje
        if ($transaction) {
            $this->db->begin_transaction();
        }

        // Przygotowanie zapytania
        $q = "
            UPDATE account 
            SET balance = balance + ?
            WHERE number = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $amount],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['number']],
        ];

        // Wykonanie zapytania, jesli niepowodzenie wycofac zmiany z DB i zwrocic blad
        if (!$this->db->update($q, $BIND)) {
            $this->db->rollback();
            $ERRORS[] = "Wystąpił błąd. Spróbuj ponownie później";
            return [false, ['account' => $ERRORS]];
        }

        // Utrwalic zmiany w DB
        if ($transaction) {
            $this->db->commit();
        }

        // zwrocic sukces i brak bledow
        return [true, []];
    }

    /**
     * Funkcja sluzy do sciagania pieniedzy z konta
     * 
     * @param double $amount
     * @param bool $transaction
     * @return array(bool, array())
     */
    public function pay($amount, $transaction = true) {
        $ERRORS = [];

        // Sprawdzanie poprawnosci operacji
        // jesli podano bledny numer lub stan konta bedzie mniejszy niz dopuszczalny debet rzucic bledem
        if (!preg_match('/^(\d{1,10})(\.|,)?(\d{0,2})$/', $amount)) {
            $ERRORS['amount'] = "Należy podać kwotę w postaci: 9999999999 lub 9999999999,99";
        } else {
            $q = "
                SELECT balance, debit
                FROM account
                WHERE number = ?
            ";

            $BIND = [
                ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['number']],
            ];

            $ROW = $this->db->fetchRow($q, $BIND);
            if ((double)$ROW['debit'] < (-1 * (double)$ROW['balance']) + (double)$amount) {
                $ERRORS['amount'] = 'Nie można zrealizować transakcji. Brak dostępnych środków';
            }
        }

        // Jesli znaleziono jakis blad zwrocic niepowodzenie z bledem
        if (!empty($ERRORS)) {
            return [false, ['account' => $ERRORS]];
        }

        // Rozpoczac transakcje
        if ($transaction) {
            $this->db->begin_transaction();
        }

        // Przygotowanie zapytania
        $q = "
            UPDATE account 
            SET balance = balance - ?
            WHERE number = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $amount],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['number']],
        ];

        // Wykonanie zapytania, jesli niepowodzenie wycofac zmiany z DB i zwrocic blad
        if (!$this->db->update($q, $BIND)) {
            $this->db->rollback();
            $ERRORS[] = "Wystąpił błąd. Spróbuj ponownie później";
            return [false, ['account' => $ERRORS]];
        }

        // Utrwalic zmiany w DB
        if ($transaction) {
            $this->db->commit();
        }

        // zwrocic sukces i brak bledow
        return [true, []];
    }

    /**
     * Funkcja sluzy do pobierania danych o wydatkach
     * 
     * @param int $days
     * @return array|bool
     */
    public function getTransferOutcome($days = 3) {
        // Zmienna ktora wyznacza ile dni pobrac z DB
        $timeLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Przygotowanie zapytania
        $q = "
            SELECT *
            FROM transfer
            WHERE sender = ? AND date >= ?
            ORDER BY date DESC
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['number']],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $timeLimit],
        ];

        // Wykonanie zapytania i zwrocenie wyniku
        return $this->db->fetchAll($q, $BIND);
    }

    /**
     * Funkcja sluzy do pobierania danych o dochodach
     * 
     * @param int $days
     * @return array|bool
     */
    public function getTransferIncome($days = 3) {
        // Zmienna ktora wyznacza ile dni pobrac z DB
        $timeLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Przygotowanie zapytania
        $q = "
            SELECT *
            FROM transfer
            WHERE reciver = ? AND date >= ?
            ORDER BY date DESC
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['number']],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $timeLimit],
        ];

        // Wykonanie zapytania i zwrocenie wyniku
        return $this->db->fetchAll($q, $BIND);
    }

    static $COUNTRIES = array('AL'=>28,'AD'=>24,'AT'=>20,'AZ'=>28,'BH'=>22,'BE'=>16,'BA'=>20,'BR'=>29,'BG'=>22,'CR'=>21,'HR'=>21,
                              'CY'=>28,'CZ'=>24,'DK'=>18,'DO'=>28,'EE'=>20,'FO'=>18,'FI'=>18,'FR'=>27,'GE'=>22,'DE'=>22,'GI'=>23,
                              'GR'=>27,'GL'=>18,'GT'=>28,'HU'=>28,'IS'=>26,'IE'=>22,'IL'=>23,'IT'=>27,'JO'=>30,'KZ'=>20,'KW'=>30,
                              'LV'=>21,'LB'=>28,'LI'=>21,'LT'=>20,'LU'=>20,'MK'=>19,'MT'=>31,'MR'=>27,'MU'=>30,'MC'=>27,'MD'=>24,
                              'ME'=>22,'NL'=>18,'NO'=>15,'PK'=>24,'PS'=>29,'PL'=>28,'PT'=>25,'QA'=>29,'RO'=>24,'SM'=>27,'SA'=>24,
                              'RS'=>22,'SK'=>24,'SI'=>19,'ES'=>24,'SE'=>24,'CH'=>21,'TN'=>24,'TR'=>26,'AE'=>23,'GB'=>22,'VG'=>24);

    static $CHARS = array('A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,'I'=>18,'J'=>19,'K'=>20,'L'=>21,'M'=>22,
                          'N'=>23,'O'=>24,'P'=>25,'Q'=>26,'R'=>27,'S'=>28,'T'=>29,'U'=>30,'V'=>31,'W'=>32,'X'=>33,'Y'=>34,'Z'=>35);

    
    /**
     * Funkcja generuje nowy numer konta i jego numer iban
     * 
     * @return array(string, string)|bool
     */
    static function generateAccountNumber() {
        // Pobranie kodu banku z opcji
        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankCode = $CONFIG['bank']['bank_code'];

        if(empty($bankCode)) {
            return false;
        }

        $lastCharacters = '252100'; // Wartosc odpowiadajaca PL00
        $accountNumber = str_pad(mt_rand(0, 99999999), 16, '0', STR_PAD_LEFT); // Generowanie numeru o dlugosci 16 znakow

        $checkNumber = 98 - self::mod($bankCode . $accountNumber . $lastCharacters, 97); // Wyznaczanie liczb kontrolnej numeru
        $iban = 'PL' . $checkNumber . $bankCode . $accountNumber; // Sklejanie calego numeru

        return [$accountNumber, $iban];
    } 

    /**
     * Funkcja sprawdza poprawnosc numeru iban
     * 
     * @param string $iban
     * @return bool
     */
    static function checkIBAN($iban) {
        // Usuniecie wszystkich spacji i zamiana liter na duze
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Sprawdzenie czy walidator obsluguje przeslany numer iban
        if(array_key_exists(substr($iban,0,2), self::$COUNTRIES) && strlen($iban) == self::$COUNTRIES[substr($iban,0,2)]){

            // Cala logika ze sprawdzeniem
            // Na poczatku przesuwamy 4 znaki numeru na koniec
            // Nastepnie zamieniamy litery na cyfry, gdzie A - 10 ... Z - 35
            // Nastepnie wyznaczamy reszte z dzielnia przez 97, jesli jest rowna 1 numer jest prawidlowy
            $movedChar = substr($iban, 4) . substr($iban,0,4);
            $MovedCharArray = str_split($movedChar);
            $NewString = "";

            foreach($MovedCharArray AS $key => $value){
                if(!is_numeric($MovedCharArray[$key]))
                    $MovedCharArray[$key] = self::$CHARS[$MovedCharArray[$key]];
                $NewString .= $MovedCharArray[$key];
            }

            if(self::mod($NewString, 97) == 1)
                return true;
        }
        return false;
    }

    /**
     * Funkcja pobiera informacja o koncie po numerze
     * 
     * @param string $user
     * @param string $number
     * @return array|bool
     */
    public static function getAccountData($number) {
        // Przygotowanie zapytania
        $q = "
            SELECT *
            FROM account
            WHERE number = ?
        ";

        $BIND[] = ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $number];

        // Wykonanie zapytania i zwrocenie wyniku
        return Db::getInstance('database')->fetchRow($q, $BIND);
    }

    /**
     * Funkcja pobiera informacja o koncie ktore jest powiazane jest z uzytkownikiem
     * 
     * @param string $user
     * @param string $number
     * @return array|bool
     */
    public static function getAccountDataByUser($user) {
        // Przygotowanie zapytania
        $q = "
            SELECT a.*
            FROM account a
            JOIN user u ON a.user_id = u.id
            WHERE u.login = ?
        ";

        $BIND[] = ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $user];

        // Wykonanie zapytania i zwrocenie wyniku
        return Db::getInstance('database')->fetchRow($q, $BIND);
    }

    /**
     * Funkcja zwracajaca reszte z dzielenia
     * Niektore liczby sa zbyt duze i nie mozna uzyc znaku %
     * 
     * @param string $x
     * @param int $y
     * @return int
     */
    public static function mod( $x, $y ) {
        // Zmienna ktora wyznacza ile znakow brac z przeslanej liczby
        $take = 5;
        $mod = '';

        while ( strlen($x) ) {
            // Wyznaczanie reszty
            $a = (int)$mod.substr( $x, 0, $take );
            $x = substr( $x, $take );
            $mod = $a % $y;   
        }

        // Zwrocenie wyniku
        return (int)$mod;
    }
}