<?php

namespace Controllers;
use Core\Controller;
use Core\Db;

class Contact extends Controller {
    public function index() {
        if ($this->isWorker || $this->isAdmin) {
            $db = Db::getInstance();

            $q = "
                SELECT *
                FROM contact
                ORDER BY id DESC
            ";

            $CONTACTS = $db->fetchAll($q);

            $this->getView('contact', 'contact', ['contact' => $CONTACTS]);
            return;
        }

        $this->getView('contact', 'index');
    }

    public function send() {
        $DATA = $this->PARAMS['POST'];
        $ERRORS = [];
        $db = Db::getInstance();

        if (empty($DATA['email']) || !filter_var($DATA['email'], FILTER_VALIDATE_EMAIL)) {
            $ERRORS['contact']['email'] = "Proszę podać prawidłowy email";
        }

        if (empty($DATA['text'])) {
            $ERRORS['contact']['email'] = "Proszę wprowadzic tekst wiadomości";
        }

        if (!empty($ERRORS)) {
            $this->getView('contact', 'index', ['contact' => $DATA, 'errors' => $ERRORS]);
            return;
        }

        $q = "
        INSERT INTO contact
        (email, text)
        VALUES(?, ?);
        ";

        $BIND = [
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $DATA['email']],
            ['mysqliBindType' => Db::MYSQLI_BIND_TYPE_STRING, 'value' => $DATA['text']]
        ];

        if ($db->insert($q, $BIND)) {
            $_SESSION['success'][] = 'Wysłano treść wiadomości';
            $this->redirect('contact', 'index');
        }

        if (!empty($ERRORS)) {
            $this->getView('contact', 'index', ['contact' => $DATA, 'errors' => $ERRORS]);
            return;
        }
    }
}

?>