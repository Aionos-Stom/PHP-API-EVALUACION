<?php

declare(strict_types=1);

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;

return [
    'default' => env('LOG_CHANNEL', 'stderr'),
    'channels' => [
        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => ['stream' => 'php://stderr'],
            'level' => env('LOG_LEVEL', 'warning'),
        ],
        'null' => ['driver' => 'monolog', 'handler' => NullHandler::class],
    ],
];
