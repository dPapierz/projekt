<?php

namespace Controllers;
use Core\Controller;

use Models\User;
use Models\Credit as C;

class Credit extends Controller {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }
    }

    public function index() {
        if ($this->isUser) {
            $CREDITS = C::getCreditsByLogin($_SESSION['user']['login']);
            $this->getView('credit', 'index', ['credit' => $CREDITS]);
        } else {
            $CREDITS = C::getPendingCredits();
            $this->getView('credit', 'all', ['credit' => $CREDITS]);
        }
    }

    public function submit() {
        $DANE = $this->PARAMS['POST'];
        if (!empty($DANE['accept'])) {
            list($success, $ERRORS) = C::addCredit($_SESSION['user']['login'], $DANE['amount'], $DANE['currency'], $DANE['installment']);
            if ($success) {
                $_SESSION['success'][] = 'Operacja zakończyła się pomyślnie';
                $this->redirect('credit', 'submit');
            }
        }

        $this->getView('credit', 'submit', ['credit' => $DANE, 'errors' => $ERRORS]);
    }

    public function show($id, $installment = null) {
        $CREDIT = C::getCreditWithInstallment($id);
        if (!empty($installment)) {
            $credit = new C($id);
            list($success, $ERRORS) = $credit->payInstallment($installment, $_SESSION['user']['login']);

            if ($success) {
                $_SESSION['success'][] = 'Zapłacono ratę';
                $this->redirect('credit', 'show', [$id]);
            }
        }

        $this->getView('credit', 'show', ['credit' => $CREDIT]);
    }

    public function accept($id) {
        if ($this->isWorker) {
            $credit = new C($id);
            list($success, $ERRORS) = $credit->acceptCredit();
            if ($success) {
                $_SESSION['success'][] = 'Przyznano kredyt';
            }

            $this->redirect('credit');
        }
    }
}

?>