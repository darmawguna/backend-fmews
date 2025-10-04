<?php

return [
    'paths' => ['api/*'], // Pastikan path API Anda tercover

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5174') // <-- AMBIL DARI .env
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // <-- INI WAJIB TRUE!
];