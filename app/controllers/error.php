<?php

namespace Controllers;
use Core\Controller;

class Error extends Controller {
    public function index() {
        $this->getView('error', 'index');
    }
}

?>