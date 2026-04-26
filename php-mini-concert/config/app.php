<?php

use App\Support\Env;

return [
    'app' => [
        'name' => Env::get('APP_NAME', 'My App'),
        'env' => Env::get('APP_ENV', 'prod'),
        'debug' => Env::bool('APP_DEBUG', false),
        'url' => Env::get('APP_URL', 'http://localhost:8000'),
        'organizer' => Env::get('ORGANIZER_NAME', 'Training Center'),
        'max_registrations_per_request' => Env::int('MAX_REGISTRATIONS_PER_REQUEST', 1),
    ],
];

?>