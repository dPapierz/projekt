<?php

namespace Controllers;

use Core\Controller;
use Models\User;

class Home extends Controller {
    public function __construct() {
        if(User::isLoged()) {
            $this->redirect('main');
        }
    }

    public function index() {
        $this->getView('home', 'index');
    }

    public function Login() {
        $login = isset($this->PARAMS['POST']['login']) ? $this->PARAMS['POST']['login'] : '';
        $password = isset($this->PARAMS['POST']['password']) ? $this->PARAMS['POST']['password'] : '';

        list($succes, $ERRORS) = User::login($login, $password);

        if($succes) {
            $this->redirect('main');
        }
        
        $this->getView('home', 'index', ['errors' => $ERRORS]);
    }
}

?>