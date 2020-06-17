<?php

namespace Core;

use Models\Role;
use ReflectionClass;

class Controller {
    protected $PARAMS = [];
    protected $isAdmin = false;
    protected $isWorker = false;
    protected $isUser = false;

    public final function process($method, $PARAMS) {
        $this->PARAMS = $PARAMS;

        if (isset($_SESSION['user']['login'])) {
            $this->isAdmin = Role::hasRight($_SESSION['user']['login'], 'admin');
            $this->isWorker = Role::hasRight($_SESSION['user']['login'], 'worker');
            $this->isUser = Role::hasRight($_SESSION['user']['login']);
        }

        if(method_exists($this, $method))
            call_user_func_array([$this, $method], $this->PARAMS['GET']);
        else
            $this->redirect('error');
    }

    public final function redirect($controller, $function = null, $PARAMS = []) {
        $url = ROOT_URL . '/' . $controller;
        if(!empty($function)) {
            $url .= '/' . $function;
            foreach($PARAMS as $param) {
                $url .= '/' . $param;
            }
        }

        header("location: {$url}");
        exit;
    }

    public final function getModel($model, $PARAMS = []) {
        $reflection_class = new ReflectionClass($model);
        return $reflection_class->newInstanceArgs($PARAMS);
    }

    public final function getView($view, $page = 'index', $DATA = []) {
        if(isset($_SESSION['success'])) {
            $DATA['success'] = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        require_once APP_PATH . DS . 'views' . DS . $view . DS . $page . '.php';
    }
}