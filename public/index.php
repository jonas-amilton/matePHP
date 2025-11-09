<?php
require_once __DIR__ . '/../vendor/autoload.php';


use Framework\Core\Request;


if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $k => $v) {
        $_ENV[$k] = $v;
    }
}


$request = new Request();
$router = require __DIR__ . '/../routes/api.php';
$router->dispatch($request);
