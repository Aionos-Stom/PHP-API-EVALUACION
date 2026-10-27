<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Productos y divisas API'),
    'env' => env('APP_ENV', 'production'),
    'debug' => false,
    'url' => env('APP_URL', 'http://127.0.0.1:8088'),
    'timezone' => 'UTC',
    'locale' => env('APP_LOCALE', 'es'),
    'fallback_locale' => 'en',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],
];
