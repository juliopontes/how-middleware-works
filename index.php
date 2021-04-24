<?php
define('BASE_PATH', __DIR__);

use App\Core\Application;

/// simple autoload based on namespace
spl_autoload_register(function ($class_name) {
    $path = str_replace('\\',DIRECTORY_SEPARATOR, $class_name) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

require_once 'App/functions.php';

Application::run();
