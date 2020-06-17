<?php

namespace Core;

use Controllers\Error;
use Exception;

session_start();
ini_set('display_errors', 0);

require_once 'loader.php';

class App {
    public function __construct() {
        $URL = $this->parseUrl();
        $controller = null;

        try {
            $controller = new $URL['controller'];
        } catch(Exception $e) {
            $controller = new Error();
            $URL['method'] = 'index';
            $URL['GET'] = [];
            $URL['POST'] = [];
        }
        $controller->process($URL['method'], ['GET' => $URL['GET'], 'POST' => $URL['POST']]);
    }

    private function parseUrl() {
        $RET = $URL = [];

        if (isset($_GET['url']))
            $URL['GET'] = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));

        if (isset($_POST))
            $URL['POST'] = $_POST;

        $RET['controller'] = !empty($URL['GET'][0]) ? 'Controllers\\' . ucfirst($URL['GET'][0]) : 'Controllers\\Home';
        $RET['method'] = !empty($URL['GET'][1]) ? $URL['GET'][1] : 'index';

        unset($URL['GET'][0], $URL['GET'][1]);

        $RET['GET'] = isset($URL['GET']) ? array_values($URL['GET']) : [];
        $RET['POST'] = isset($URL['POST']) ? $URL['POST'] : [];

        return $RET;
    }
}

?>