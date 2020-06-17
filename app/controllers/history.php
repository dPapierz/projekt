<?php

namespace Controllers;

use Core\Controller;

use Models\Account;
use Models\User;

class History extends Controller {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }
    }

    public function index() {
        $ACCOUNT = Account::getAccountDataByUser($_SESSION['user']['login']);
        $account = new Account($ACCOUNT['number']);

        $TRANSFER['outcome'] = $account->getTransferOutcome();
        $TRANSFER['income'] = $account->getTransferIncome();

        $this->getView('history', 'index', ['account' => $ACCOUNT, 'transfer' => $TRANSFER]);
    }

    public function archive($days) {
        $time = intval($days);
        if (!$time || $time < 0) {
            $time = 3;
        }

        $ACCOUNT = Account::getAccountDataByUser($_SESSION['user']['login']);
        $account = new Account($ACCOUNT['number']);

        $TRANSFER['outcome'] = $account->getTransferOutcome($time);
        $TRANSFER['income'] = $account->getTransferIncome($time);

        $this->getView('history', 'index', ['account' => $ACCOUNT, 'transfer' => $TRANSFER]);
    }
}

?>