<?php

define('DS', DIRECTORY_SEPARATOR);

define('ROOT_PATH', dirname(dirname(__FILE__)));
define('ROOT_URL', dirname(dirname($_SERVER['PHP_SELF'])));

define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
define('APP_PATH', ROOT_PATH . DS . 'app');


?>