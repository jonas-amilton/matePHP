<?php

return [
    'default' => 'app',

    'channels' => [
        'app' => [
            'driver' => 'single',
            'path' => __DIR__ . '/../../storage/logs/app.log',
            'level' => 'debug',
        ],
        'errors' => [
            'driver' => 'single',
            'path' => __DIR__ . '/../../storage/logs/errors.log',
            'level' => 'error',
        ],
        'console' => [
            'driver' => 'stdout',
            'level' => 'info',
        ],
    ],
];
