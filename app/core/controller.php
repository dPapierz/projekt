<?php

namespace Core;

use ReflectionClass;

class Controller {
    protected $PARAMS = [];

    public final function process($method, $PARAMS) {
        $this->PARAMS = $PARAMS;
        if(method_exists($this, $method))
            call_user_func_array([$this, $method], $this->PARAMS['GET']);
    }

    public final function getModel($model, $PARAMS = []) {
        $reflection_class = new ReflectionClass($model);
        return $reflection_class->newInstanceArgs($PARAMS);
    }

    public final function getView($view, $page = 'index', $DATA = []) {
        require_once APP_PATH . DS . 'views' . DS . $view . DS . $page . '.php';
    }
}