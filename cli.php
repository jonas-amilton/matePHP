<?php
require_once __DIR__ . '/vendor/autoload.php';


if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $k => $v) {
        $_ENV[$k] = $v;
    }
}


\Framework\Console\Kernel::handle($argv);
