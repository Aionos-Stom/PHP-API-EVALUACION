<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_STORE', 'database'),
    'stores' => [
        'database' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'cache',
            'lock_connection' => null,
            'lock_table' => 'cache_locks',
        ],
        'array' => ['driver' => 'array', 'serialize' => false],
    ],
    'prefix' => 'productos_divisas_',
];
