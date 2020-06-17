<?php

namespace Models;

use Core\Model;
use Core\Db;

use Models\Account;

class Transfer extends Model { 

    /**
     * Funckja dodaje nowy przelew do DB
     * 
     * @param string $sender
     * @param string $reciver
     * @param string $title
     * @param double $amount
     * @param string|null $name
     * @param string|null $description
     * @param string|null $address
     * @param bool $transaction
     * @return array(bool, array())
     */
    public static function add($sender, $reciver, $title, $amount, $name = null, $description = null, $address = null, $transaction = true) {
        $db = Db::getInstance('database');
        $ERRORS = [];
        
        // Sprawdzenie wszystkich zmiennych pod katem poprawnosci
        if (!Account::checkIBAN($sender)) {
            $ERRORS['sender'] = "Numer konta nadawcy przelewu jest nieprawidłowy.";
        }

        if (!Account::checkIBAN($reciver)) {
            $ERRORS['reciver'] = "Numer konta odbiorcy przelewu jest nieprawidłowy.";
        }

        if ($sender === $reciver) {
            $ERRORS['reciver'] = "Numer konta odbiorcy przelewu jest taki sam jak nadawcy";
        }

        if (empty($title) || strlen($title) > 100) {
            $ERRORS['title'] = "Należy podać tytuł przelewu z maksymalnie 100 znaków.";
        }

        if (!preg_match('/^(\d{1,10})(\.|,)?(\d{0,2})$/', $amount)) {
            $ERRORS['amount'] = "Należy podać kwotę w postaci: 9999999999 lub 9999999999,99";
        }

        if(strlen($name) > 100) {
            $ERRORS['name'] = "Nazwa odbiorcy nie może przekraczać 100 znaków.";
        }

        if(strlen($address) > 100) {
            $ERRORS['address'] = "Adres odbiorcy nie może przekraczać 100 znaków.";
        }

        if(strlen($description) > 100) {
            $ERRORS['description'] = "Opis nie może przekraczać 255 znaków.";
        }

        if(!empty($ERRORS)) {
            return [false, ['transfer' => $ERRORS]];
        }

        // Stworzenie obiektow kont na ktorych bedziemy dzialac
        $sender_account = new Account($sender);
        $reciver_account = new Account($reciver);

        // Rozpoczecie transakcji
        if ($transaction) {
            $db->begin_transaction();
        }

        // Przygotowanie zapytania
        $q = "
            INSERT INTO transfer
            (sender, reciver, title, amount, name, description, address)
            VALUES(?, ?, ?, ?, ?, ?, ?);
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $sender],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $reciver],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $title],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_DOUBLE, 'value' => $amount],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $name],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $description],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $address],
        ];


        // Dodanie nowego przelewu, w wypadku niepowodzenia wycofanie zmian w DB i zwrocenie bledu
        if (!$db->insert($q, $BIND)) {
            $db->rollback();
            return [false, ['transfer' => $ERRORS]];
        }

        // Zmniejszenie stanu konta platnika, w wypadku niepowodzenia wycofanie zmian w DB i zwrocenie bledu
        list($success, $ERRORS) = $sender_account->pay($amount, false);
        if (!$success) {
            $db->rollback();
            return [false, $ERRORS];
        }

        // Zwiekszenie stanu konta odbiorcy, w wypadku niepowodzenia wycofanie zmian w DB i zwrocenie bledu
        list($success, $ERRORS) = $reciver_account->deposit($amount, false);
        if (!$success) {
            $db->rollback();
            return [false, $ERRORS];
        }

        // Utrwalenie zmian w DB i zwrocenie sukcesu
        if ($transaction) {
            $db->commit();
        }

        return [true, []];
    }
}