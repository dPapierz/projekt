<?php

namespace Controllers;
use Core\Controller;

class Help extends Controller {
    public function index() {
        $this->getView('help', 'index');
    }
}

?>