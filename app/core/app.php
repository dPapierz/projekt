<?php

namespace Core;

session_start();

require_once 'loader.php';

class App {
    public function __construct() {
        $URL = $this->parseUrl();
        
        $controller = new $URL['controller'];
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