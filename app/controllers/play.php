<?php

namespace Controllers;
use Models\User;

class Play extends Topup {
    public function __construct() {
        if(!User::isLoged()) {
            $this->redirect('home');
        }

        $CONFIG = parse_ini_file('config' . DS . 'main.ini', true);
        $bankAccount = $CONFIG['play']['account'];

        $this->operator = 'Play';
        $this->bankAccount = $bankAccount;
        $this->description = 'Doładowanie telefonu';
    }

    protected function getPage() {
        return 'play';
    }
}

?>