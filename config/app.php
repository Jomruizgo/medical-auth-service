<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'medical-auth-msvc',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'port' => (int) ($_ENV['APP_PORT'] ?? 8001),

    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? '',
        'algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
        'access_expiration' => (int) ($_ENV['JWT_ACCESS_EXPIRATION'] ?? 3600),
        'refresh_expiration' => (int) ($_ENV['JWT_REFRESH_EXPIRATION'] ?? 604800),
    ],

    'cors' => [
        'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'),
    ],
];
