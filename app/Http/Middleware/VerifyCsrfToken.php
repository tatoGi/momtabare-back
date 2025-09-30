<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'register',
        'send-registration-email',
        'verify-email',
        'verify-email/*',
        'resend-verification',
        'resend-email-verification',
        'complete-registration',
        'login',
        'logout',
        'api/*',
        '*/send-registration-email*',
        '*/verify-email*',
        '*/resend-verification*',
        '*/complete-registration*',
        '*/login*',
        '*/logout*',
        '*/register',
        '*/login',
        '*/logout',
        '*/add-to-cart',
        '*/remove-from-cart',
        '*/add-to-wishlist',
        '*/remove-from-wishlist',
        '*/subscribe',
        '*/contact-submit',
        '*/home',
        '*/pages',
        '*/{slug}',
        '*/pro/{url}',
        '*/clear-optimization',
        '*/search',
        '*/wishlist',
        '*/locale/sync',
        '*/auth/send-welcome-email',
        '*/profile',
        '*/profile/*',
        '*/retailer/*',
        '*/products/*/comments',
        '*/products/*/comments/*',
        'products/*/comments',
        'products/*/comments/*',
        '*/update-cart-item',
        '*/bog/orders',
        '*/bog/token',
        '*/bog/orders/*',
    ];
}
