<?php

declare(strict_types=1);

return [
    'token_hash' => env('API_TOKEN_HASH', ''),
    'rate_limit' => (int) env('API_RATE_LIMIT', 60),
    'max_body_bytes' => (int) env('API_MAX_BODY_BYTES', 16384),
];
