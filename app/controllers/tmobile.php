<?php

namespace Controllers;
use Models\User;

class Tmobile extends Topup {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }

        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankAccount = $CONFIG['tmobile']['account'];

        $this->operator = 'T-mobile';
        $this->bankAccount = $bankAccount;
        $this->description = 'Doładowanie telefonu';
    }

    protected function getPage() {
        return 'tmobile';
    }
}

?>