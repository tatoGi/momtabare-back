<?php

// config/cors.php
return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'en/*',
        'ka/*',
        'me',
        'en/me',
        'ka/me',
        'admin/*'
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://momtabare-front.vercel.app',
        'https://admin.momtabare.com',
          'https://momtabare.com',
            'https://www.momtabare.com' 
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
