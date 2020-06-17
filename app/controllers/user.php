<?php

namespace Controllers;
use Core\Controller;
use Models\Account;
use Models\Role;
use Models\User as U;

class User extends Controller {
    public function __construct() {
        if(!U::isLoged()) {
            $this->redirect('home');
        }

        if(!(Role::hasRight($_SESSION['user']['login'], 'admin') || Role::hasRight($_SESSION['user']['login'], 'worker'))) {
            $this->redirect('main');
        }
    }

    public function index() {
        $USERS = U::getUsers();

        $this->getView('user', 'index', ['users' => $USERS, 'meta' => ['admin' => $this->isAdmin, 'worker' => $this->isWorker]]);
    }

    public function show($id) {
        $USER = U::getUser($id);
        $USER['role'] = Role::getRight($USER['login']);
        $ACCOUNT = Account::getAccountDataByUser($USER['login']);

        $this->getView('user', 'user', ['user' => $USER, 'account' => $ACCOUNT, 'meta' => ['id' => $id,'admin' => $this->isAdmin, 'worker' => $this->isWorker]]);
    }

    public function change($id) {
        $ERRORS = [];
        $DANE = $this->PARAMS['POST'];

        $USER = U::getUser($id);
        $ROLE = Role::getRoleByLogin($USER['login']);

        $USER['role'] = $ROLE['role'];
        $ACCOUNT = Account::getAccountDataByUser($USER['login']);

        $user = new U($id);
        $account = new Account($ACCOUNT['number']);

        if (!empty($DANE['user'])) {
            $USER = array_merge($USER, $DANE);
            if ($DANE['password'] !== $DANE['password2']) {
                $ERRORS['user']['password2'] = "Hasła nie są zgodne";
            } else {
                list($success, $ERRORS) = $user->changeUser($USER['password'], $USER['name'], $USER['surname'], $USER['role'], $USER['active']);
                if ($success) {
                    $_SESSION['success'][] = 'Aktualizacja powiodła się';
                    $this->redirect('user', 'show', [$id]);
                }
            }
        }

        if (!empty($DANE['account'])) {
            $ACCOUNT = array_merge($ACCOUNT, $DANE);
            list($success, $ERRORS) = $account->changeAccount($ACCOUNT['balance'], $ACCOUNT['debit'], $ACCOUNT['currency']);
            if ($success) {
                $_SESSION['success'][] = 'Aktualizacja powiodła się';
                $this->redirect('user', 'show', [$id]);
            }
        }

        $this->getView('user', 'user', ['errors' => $ERRORS, 'user' => $USER, 'account' => $ACCOUNT, 'meta' => ['id' => $id, 'admin' => $this->isAdmin, 'worker' => $this->isWorker]]);
    }
}

?>