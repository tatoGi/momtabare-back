<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'bog' => [
        'auth_url' => env('BOG_AUTH_URL'),
        'orders_url' => env('BOG_ORDERS_URL', 'https://api.bog.ge/payments/v1/ecommerce/orders'),
        'receipt_url' => env('BOG_RECEIPT_URL', 'https://api.bog.ge/payments/v1/receipt'),
        'callback_url' => env('BOG_CALLBACK_URL', 'https://your-domain.com/bog/callback'),
        'client_id' => env('BOG_CLIENT_ID'),
        'client_secret' => env('BOG_CLIENT_SECRET'),
    ],

];
