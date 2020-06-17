<?php

namespace Models;

use Core\Db;
use Core\Model;

class Role extends Model {
    const ROLE_USER = 'user';
    const ROLE_WORKER = 'worker';
    const ROLE_ADMIN = 'admin';

    /**
     * Funkcja dodaje role uzytkownikowi
     * 
     * @param string $role
     * @param string $login
     * @param bool $transaction
     * @return array(bool, array())
     */
    public static function addToRole($role, $login, $transaction = true) {
        $ERRORS = [];

        // Sprawdzenie czy znaleziono bledy
        if(!in_array($role, [self::ROLE_USER, self::ROLE_WORKER, self::ROLE_ADMIN])) {
            $ERRORS['role'] = "Przesłana rola nie istnieje";
        }

        $USER = User::getUsers($login);
        if (empty($USER)) {
            $ERRORS['user'] = "Użytkownik nie istnieje";
        }

        if(!empty($ERRORS)) {
            return [false, ['role' => $ERRORS]];
        }

        // Przygotowanie zapytania
        $db = Db::getInstance();

        if ($transaction) {
            $db->begin_transaction();
        }

        $q = "
        INSERT INTO roles
        (user_id, role)
        VALUES(?,?);
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $USER['id']],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $role],
        ];

        // Wykonanie zapytania
        if(!$db->insert($q, $BIND)) {
            // Niepowodzenie, wycofac zmiany
            $db->rollback();
            $ERRORS[] = "Nie udało się dodać użytykownika do roli";
            return [false, ['role' => $ERRORS]];
        }

        // Utrwalic zmiany w bazie
        if ($transaction) {
            $db->commit();
        }

        return [true, []];
    }

    /**
     * Funkcja zmienia role uzytkownika
     * 
     * @param string $role
     * @param string $login
     * @param bool $transaction
     * @return array(bool, array())
     */
    public static function changeRole($role, $login, $transaction = true) {
        $ERRORS = [];

        // Sprawdzenie bledow
        if(!in_array($role, [self::ROLE_USER, self::ROLE_WORKER, self::ROLE_ADMIN])) {
            $ERRORS['role'] = "Przesłana rola nie istnieje";
        }

        $USER = User::getUsers($login);
        $ROLE = [];
        if (empty($USER)) {
            $ERRORS['user'] = "Użytkownik nie istnieje";
        } else {
            $ROLE = self::getRoleByLogin($login);

            if (empty($ROLE)) {
                $ERRORS[] = 'Nie znaleziono roli';
            } else {
                // Nie mozna zmienic roli uzytkownika na pracownika/admina i na odwrót
                if ($ROLE['role'] === self::ROLE_USER && $ROLE['role'] !== self::ROLE_USER ||
                    $ROLE['role'] !== self::ROLE_USER && $ROLE['role'] === self::ROLE_USER) {
                    $ERRORS['role'] = 'Nie można zmienić roli';
                }
            }
        }

        if(!empty($ERRORS)) {
            return [false, ['role' => $ERRORS]];
        }

        // Przygotowanie zapytania
        $db = Db::getInstance();

        if ($transaction) {
            $db->begin_transaction();
        }

        $q = "
        UPDATE roles
        SET role = ?
        WHERE user_id = ?;        
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $role],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $USER['id']],
        ];

        // Wykonanie zapytania
        if(!$db->insert($q, $BIND)) {
            // Niepowodzenie, wycofanie zmian
            $db->rollback();
            $ERRORS[] = "Nie udało się dodać użytykownika do roli";
            return [false, ['role' => $ERRORS]];
        }

        // Utrwalenie zmian w bazie
        if ($transaction) {
            $db->commit();
        }

        return [true, []];
    }


    /**
     * Funkcja zwraca role z bazy danych
     * 
     * @param int $id
     * @return array|bool
     */
    public static function getRole($id) {
        $q = "
            SELECT *
            FROM roles
            WHERE id = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_INTEGER, 'value' => $id]
        ];

        return Db::getInstance()->fetchRow($q, $BIND);
    }


    /**
     * Funkcja zwraca role z bazy danych przypisana do uzytkownika
     * 
     * @param string $login
     * @return array|bool
     */
    public static function getRoleByLogin($login) {
        $q = "
            SELECT r.*
            FROM roles r
            JOIN user u ON r.user_id = u.id
            WHERE u.login = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]
        ];

        return Db::getInstance()->fetchRow($q, $BIND);
    }

    /**
     * Funkcja sprawdza czy uzytkownik posiada prawo
     * 
     * @param string $login
     * @param string $right
     * @return bool
     */
    public static function hasRight($login, $right = 'user') {
        $q = "
            SELECT COUNT(*)
            FROM roles r
            JOIN user u ON r.user_id = u.id
            WHERE u.login = ? AND r.role = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $right]
        ];

        return (bool)Db::getInstance()->fetchOne($q, $BIND);
    }


    /**
     * Funkcja zwraca prawo uzytkownika
     * 
     * @param string $login
     * @return bool
     */
    public static function getRight($login) {
        $q = "
            SELECT r.role
            FROM roles r
            JOIN user u ON r.user_id = u.id
            WHERE u.login = ?
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $login]
        ];

        return Db::getInstance()->fetchOne($q, $BIND);
    }
}