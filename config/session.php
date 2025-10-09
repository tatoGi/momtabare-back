<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'connection' => env('SESSION_CONNECTION', null),
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE', null),
    'lottery' => [2, 100],

    'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', null),
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
];
