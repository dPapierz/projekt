<?php

namespace Models;

use Core\Db;
use Core\Model;

class User extends Model {

    private $_DATA = [
        'id' => null,
        'login' => null,
        'name' => null,
        'surname' => null,
        'active' => null,
        'lastLogin' => null,
        'failedLogin' => null,
    ];

    public function __construct($id = null, $login = null) {
        parent::__construct();
        $USER = [];

        // Pobierz dane dla podanego id lub loginu
        if (!empty($id)) {
            $USER = self::getUser($id);
        }

        if (empty($USER) && !empty($login)) {
            $USER = self::getUsers($login);
        }

        // Ustaw zmienne w $_DATA
        if (!empty($USER)) {
            foreach ($USER as $key => $value) {
                if (array_key_exists($key, $this->_DATA)) {
                    $this->_DATA[$key] = $value;
                }
            }
        }
    }

    /**
     * Funckcja dodaje nowego użytkownika do systemu
     * 
     * @param string $login
     * @param string $password
     * @param string $name
     * @param string $surname
     * @param string $role
     * @param bool $active
     * @param bool $transaction
     * @return array(bool,array())
     */
    public function addUser($login, $password, $name, $surname, $role, $active = true, $transaction = true) {
        $ERRORS = [];
        if (!empty($this->_DATA['id'])) {
            $ERRORS['user'] = 'Użytkownik już istnieje';
        }

        // Walidacja przeslanych zmiennych
        if (empty($login) || strlen($login) > 15) {
            $ERRORS['login'] = 'Należy podać login z maksymalnie 15 znaków.';
        }

        if (preg_replace("/[^a-zA-Z0-9]+/", "", $login) !== $login) {
            $ERRORS['login'] = 'Należy podać login zawierający jedynie znaki alfanumeryczne';
        }

        if (self::getUsers($login)) {
            $ERRORS['login'] = 'Podany login już istnieje';
        }

        $password_hash = null;
        if (empty($password)) {
            $ERRORS['password'] = 'Należy podać hasło';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }

        if (empty($name) || strlen($name) > 40) {
            $ERRORS['name'] = 'Należy podać imię z maksymalnie 40 znaków.';
        }

        if (empty($surname) || strlen($surname) > 60) {
            $ERRORS['surname'] = 'Należy podać nazwisko z maksymalnie 60 znaków.';
        }

        if (empty($role) || !in_array($role, ['user', 'worker', 'admin'])) {
            $ERRORS['role'] = 'Należy podać prawidłową rolę';
        }

        if (!empty($ERRORS)) {
            return [false, ['user' => $ERRORS]];
        }

        // Przygotowanie zapytania
        if ($transaction) {
            $this->db->begin_transaction();
        }

        $q = "
        INSERT INTO user
        (login, password, name, surname, active)
        VALUES( ?, ?, ?, ?, ?);
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $password_hash],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $name],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $surname],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_BOOL, 'value' => $active === 'on'],
        ];

        // Wykonanie zapytania
        if(!$this->db->insert($q, $BIND)) {
            // Blad, wycofanie zmian
            $this->db->rollback();
            $ERRORS[] = "Nie udało się dodać użytykownika";
            return [false, ['user' => $ERRORS]];
        }

        // Dodanie roli do uzytkownika
        list($success, $ERRORS) = Role::addToRole($role, $login, false);
        if(!$success) {
            $this->db->rollback();
            return [false, $ERRORS];
        }

        // Stworzenie nowego konta jezeli zakladamy zwyklego uzytkownika systemu
        if ($role === Role::ROLE_USER) {
            $account = new Account();
            list($success, $ERRORS) = $account->addAccount($login, false);
            if(!$success) {
                $this->db->rollback();
                return [false, $ERRORS];
            }
        }

        // Utrwalenie zmian w bazie
        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja zmienia dane uzytkownika
     * 
     * @param string $password
     * @param string $name
     * @param string $surname
     * @param string $role
     * @param bool $active
     * @param bool $transaction
     * @return array(bool,array())
     */
    public function changeUser($password, $name, $surname, $role, $active = true, $transaction = true) {
        $ERRORS = [];

        // Sprawdzenie poprawnosci przeslanych danych
        if (empty($this->_DATA['id'])) {
            $ERRORS['user'] = 'Nie można zmienić danych, brak użytkownika';
        }

        $USER = self::getUser($this->_DATA['id']);
        $password_hash = null;

        if (empty($password)) {
            $password_hash = $USER['password'];
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }

        if (empty($name) || strlen($name) > 40) {
            $ERRORS['name'] = 'Należy podać imię z maksymalnie 40 znaków.';
        }

        if (empty($surname) || strlen($surname) > 60) {
            $ERRORS['surname'] = 'Należy podać nazwisko z maksymalnie 60 znaków.';
        }

        if (empty($role) || !in_array($role, ['user', 'worker', 'admin'])) {
            $ERRORS['role'] = 'Należy podać prawidłową rolę';
        }

        if (!empty($ERRORS)) {
            return [false, ['user' => $ERRORS]];
        }

        // Przygotowanie zapytania
        if ($transaction) {
            $this->db->begin_transaction();
        }

        $q = "
        UPDATE user
        SET password = ?, modData = ?, name = ?, surname = ?, active = ?
        WHERE id = ?;
        
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $password_hash],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => date('Y-m-d H:i:s')],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $name],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $surname],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_BOOL, 'value' => $active === 'on'],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $this->_DATA['id']],
        ];

        // Wykonanie zapytania
        if(!$this->db->update($q, $BIND)) {
            // Blad, wycofanie zmian
            $this->db->rollback();
            $ERRORS[] = "Nie udało się dodać użytykownika";
            return [false, ['user' => $ERRORS]];
        }

        // Zmiana roli uzytkownika
        list($success, $ERRORS) = Role::changeRole($role, $USER['login'], false);
        if(!$success) {
            $this->db->rollback();
            return [false, $ERRORS];
        }

        // Utrwalenie zmian w bazie
        if ($transaction) {
            $this->db->commit();
        }

        return [true, []];
    }

    public function getData($key) {
        return isset($this->_DATA[$key]) ? $this->_DATA[$key] : null;
    }

    /**
     * Pobiera uzytkownika o podanym id
     * 
     * @param string|null $login
     * @return array|bool
     */
    public static function getUser($id) {
        $q = "
            SELECT *
            FROM user
            WHERE id = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $id],
        ];

        return Db::getInstance()->fetchRow($q, $BIND);
    }

    /**
     * Pobiera wszystkich uzytkownikow lub uzytkownika o podanym loginie
     * 
     * @param string|null $login
     * @return array|bool
     */
    public static function getUsers($login = null) {
        $q = "
            SELECT *
            FROM user
        ";

        if (!empty($login)) {
            $q .= " WHERE login LIKE ?";
            $BIND = [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]];

            return Db::getInstance()->fetchRow($q, $BIND);
        }

        return Db::getInstance()->fetchAll($q);
    }

    /**
     * Funkcja loguje uzytkownika do systemu
     * 
     * @param string $login
     * @param string $password
     * @return array(bool, array())
     */
    public static function login($login, $password) {
        // Pobranie aktywnego uzytkownika z DB
        $db = Db::getInstance('database');

        $q = "
            SELECT *
            FROM user
            WHERE login = ? AND active = 1
        ";

        // Jesli nie znaleziono zwrocenie bledu
        $ROW = $db->fetchRow($q, [['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]]);
        if (!$ROW) {
            return [false, ['user' => ['Błędna nazwa użytkownika lub hasło']]];
        }

        // Jesli znaleziono ale bledne haslo
        if (!password_verify($password, $ROW['password'])) {
            $ERRORS = [];
            // Pobierz opcje z konfiguracji i zaktualizuj informacje o uzytkowniku
            // Zwieksz ilosc nieudanych logowan.
            // Jesli ilosc nieudanych logowan jest wieksza od ilosci z opcji zablokuj konto i zwroc blad.
            $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);

            $failedLogin = (int)$ROW['failedLogin'] + 1;
            $active = 1;

            $q  = "
                UPDATE user
                SET failedLogin = ?, active = ?
                WHERE login = ?
            ";


            if ($CONFIG['login']['max_attempt'] <= $failedLogin) {
                $active = 0;
                $ERRORS[] = "Konto zostało zablokowane. Za dużo prób logowania";
            }

            $db->update($q, [
                ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $failedLogin],
                ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $active],
                ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]
            ]);

            $ERRORS[] = 'Błędna nazwa uzytkownika lub hasło';
            return [false, ['user' => $ERRORS]];
        }

        // Jesli udalo sie zalogowac, ustaw dane w sesji i wyzeruj ilosc nieudanych logowan
        $_SESSION['user']['login'] = $ROW['login'];
        $_SESSION['user']['name'] = $ROW['name'];
        $_SESSION['user']['surname'] = $ROW['surname'];

        $q = "
            UPDATE user
            SET lastLogin = ?, failedLogin = 0
            WHERE login = ?
        ";

        $db->update($q, [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => date('Y-m-d H:i:s')],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]
        ]);

        return [true, []];
    }

    /**
     * Wyloguj uzytkownika z systemu; Usuwa cala sesje
     */
    public static function logout() {
        $_SESSION = [];
    }

    /**
     * Sprawdz czy uzytkownik jest zalogowany na podstawie sesji
     */
    public static function isLoged() {
        return isset($_SESSION['user']);
    }
}