<?php

namespace Controllers;
use Core\Controller;

use Models\Transfer;
use Models\Account;
use Models\User;

class Topup extends Controller {
    protected $operator;
    protected $bankAccount;
    protected $description;

    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }

        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankAccount = $CONFIG['inni']['account'];

        $this->operator = 'Inni';
        $this->bankAccount = $bankAccount;
        $this->description = 'Doładowanie telefonu';
    }

    public function index() {
        $this->getView('topup', $this->getPage());
    }

    public function topup() {
        $DANE = $this->PARAMS['POST'];
        $ACCOUNT = Account::getAccountDataByUser($_SESSION['user']['login']);

        $this->description .= ' ' . $DANE['phone'] . ' ' . $this->operator;
        list($success, $ERRORS) = Transfer::add($ACCOUNT['number'], $this->bankAccount, $this->operator, $DANE['amount'], $this->operator, $this->description, null);

        if ($success) {
            $_SESSION['success'][] = "Udało się doładować telefon";
            $this->redirect($this->getPage());
        }

        $RET['form'] = $this->PARAMS['POST'];
        $RET['error'] = $ERRORS;

        $this->getView('topup', $this->getPage(), $RET);
    }

    protected function getPage() {
        return 'index';
    }
}

?>