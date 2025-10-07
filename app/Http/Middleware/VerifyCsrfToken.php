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
        
        // Admin routes
        '*/admin/*',
        'admin/*',
        '*/admin',
        'admin',
        
        // API routes if you have them
        'api/*',
        
        // Webhook endpoints
        'webhook/*',
        '*/webhook/*',
        
        // File uploads
        '*/upload',
        'upload/*',
        
        // Media library if you're using it
        'media/*',
        '*/media/*',
    ];
}
