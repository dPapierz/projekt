<?php

namespace Controllers;
use Core\Controller;

use Models\Account;
use Models\Transfer as T;
use Models\User;

class Transfer extends Controller {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }
    }

    public function index() {
        $ACCOUNT = Account::getAccountDataByUser($_SESSION['user']['login']);
        $RET['account'] = $ACCOUNT;

        $this->getView('transfer', 'index', $RET);
    }

    public function accept() {
        $DANE = $this->PARAMS['POST'];
        $ACCOUNT = Account::getAccountDataByUser($_SESSION['user']['login']);

        list($success, $ERRORS) = T::add($ACCOUNT['number'], $DANE['reciver'], $DANE['title'], $DANE['amount'], $DANE['name'], $DANE['address']);
        
        if ($success) {
            $_SESSION['success'][] = 'Operacja zakończyła się pomyślnie';
            $this->redirect('transfer');
        }

        $RET['account'] = $ACCOUNT;
        $RET['form'] = $this->PARAMS['POST'];

        foreach($ERRORS as $key => $ERROR) {
            if($key === 'transfer') {
                $RET['errors'][$key] = $ERROR;
                continue;
            }
            $RET['errors'] = $ERROR;
        }

        $this->getView('transfer', 'index', $RET);
    }
}

?>