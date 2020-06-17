<?php

namespace Controllers;

use Core\Controller;
use Models\Account;
use Models\User;
use Models\Role;

class Main extends Controller {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }
    }

    public function index() {
        if(Role::hasRight($_SESSION['user']['login'], 'admin') || Role::hasRight($_SESSION['user']['login'], 'worker')) {
            $this->getView('main', 'admin');
        } else {
            $ACCOUNT = Account::getAccountDataByUser($_SESSION['user']['login']);
            if($ACCOUNT) {
                $account = new Account($ACCOUNT['number']);

                $TRANSFER['outcome'] = $account->getTransferOutcome();
                $TRANSFER['income'] = $account->getTransferIncome();

                $this->getView('main', 'index', ['account' => $ACCOUNT, 'transfer' => $TRANSFER]);
            }

            $this->getView('main', 'index');
        }
    }

    // Nie usuwac
    public function logout() {
        User::logout();
        $this->redirect('home');
    }
}

?>