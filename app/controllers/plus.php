<?php

namespace Controllers;
use Models\User;

class Plus extends Topup {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }

        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankAccount = $CONFIG['plus']['account'];

        $this->operator = 'Plus';
        $this->bankAccount = $bankAccount;
        $this->description = 'Doładowanie telefonu';
    }

    protected function getPage() {
        return 'plus';
    }
}

?>