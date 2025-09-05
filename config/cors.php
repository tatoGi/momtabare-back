<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
        'password/*',
        'email/verify/*',
        'forgot-password',
        'reset-password',
        'user',
        'me',
        '*/me',
        'profile',
        '*/profile*',
        'ka/*',
        'en/*',
        'ru/*',
        'sanctum/*',
        'api',
        'api/*',
        'ka',
        'en',
        'ru',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        'http://127.0.0.1:8000',
        'http://localhost:8000',
    ],

    'allowed_origins_patterns' => [
        'http://localhost:.*',
        'http://127.0.0.1:.*',
        'http://localhost',
        'http://127.0.0.1',
    ],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        'Authorization',
        'X-Localization',
        'Accept',
        'X-Socket-Id',
        'X-Sanctum-Guard',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        'X-Requested-With',
        'Accept',
        'Origin',
        'Content-Type',
        'X-Auth-Token',
    ],

    'exposed_headers' => [
        'Authorization',
        'X-Localization',
        'X-Sanctum-Guard',
        'XSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
    ],

    'max_age' => 60 * 60 * 2, // 2 hours

    'supports_credentials' => true,

];
