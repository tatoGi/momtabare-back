<?php

// use Spatie\Csp\Directive;
// use Spatie\Csp\Keyword;

return [
    'policies' => [
        'default' => \App\Policies\CustomCspPolicy::class,
    ],
    'enabled' => env('CSP_ENABLED', true),
    'report_only' => env('CSP_REPORT_ONLY', true),
    'report_uri' => null,
];
