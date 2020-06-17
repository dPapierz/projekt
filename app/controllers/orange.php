<?php

namespace Controllers;
use Models\User;

class Orange extends Topup {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }

        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankAccount = $CONFIG['orange']['account'];

        $this->operator = 'Orange';
        $this->bankAccount = $bankAccount;
        $this->description = 'Doładowanie telefonu';
    }

    protected function getPage() {
        return 'orange';
    }
}

?>