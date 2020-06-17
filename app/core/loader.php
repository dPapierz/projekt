<?php

function classLoader($className)
{
    $className = trim($className, '\\');
    $fileName = '';
    $namespace = '';

    if ($lastPos = strpos($className, '\\')) {
        $namespace = strtolower(substr($className, 0, $lastPos));
        $className = strtolower(substr($className, $lastPos + 1));
        $fileName = str_replace('\\', '/', $namespace) . '/' . $className;
    }

    if (file_exists(APP_PATH . DS . $fileName . '.php'))
        require_once APP_PATH . DS . $fileName . '.php';
    else 
        throw new Exception('Nie znaleziono klasy');
}

spl_autoload_register('classLoader');

?>