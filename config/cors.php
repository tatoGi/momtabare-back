<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'en/*', 'ka/*', 'me', 'en/me', 'ka/me'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173','https://momtabare-front.vercel.app'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,  // Important for credentials
];