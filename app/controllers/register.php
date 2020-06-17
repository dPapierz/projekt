<?php

namespace Controllers;
use Core\Controller;
use Models\User;

class Register extends Controller {
    public function index() {
        $this->getView('register', 'index');
    }

    public function add() {
        $DANE = $this->PARAMS['POST'];
        $user = new User();

        if ($DANE['password'] !== $DANE['password2']) {
            $ERRORS['user']['password2'] = "Hasła nie są zgodne";
        } else {
            list($success, $ERRORS) = $user->addUser($DANE['login'], $DANE['password'], $DANE['name'], $DANE['surname'], $DANE['role'], $DANE['active']);
            if ($success) {
                $_SESSION['success'][] = 'Dodano nowego użytkownika';
                $this->redirect('register');
            }
        }

        $this->getView('register', 'index', ['register' => $DANE, 'errors' => ['register' => $ERRORS['user']]]);
    }
}

?>