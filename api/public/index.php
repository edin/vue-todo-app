<?php

use App\Application;

spl_autoload_register(function($className) {
    $parts = explode("\\", $className);
    array_shift($parts);
    $path = implode(DIRECTORY_SEPARATOR, $parts);
    require("../src/{$path}.php");
});

$app = new Application();
$app->run();