<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $this->getAllowedOrigin($request);

        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204)  // 204 No Content
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN, Accept, X-Localization, X-Socket-ID')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400')
                ->header('Vary', 'Origin');
        }

        $response = $next($request);

        // Only add CORS headers if we have a valid response
        if ($response && method_exists($response, 'header')) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN, Accept, X-Localization, X-Socket-ID');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');
        }

        return $response;
    }

    /**
     * Get the allowed origin based on the request
     */
    private function getAllowedOrigin(Request $request): string
    {
        $origin = $request->headers->get('Origin');

        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:5173',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:5173',
            'https://*.momtabare.com',
            'https://momtabare.com',
            'http://momtabare.com',
            'https://momtabare-front.vercel.app',
        ];

        // Add production domain if set
        if (env('APP_DOMAIN')) {
            $allowedOrigins[] = 'https://' . env('APP_DOMAIN');
        }

        // If no origin header (like Postman or direct requests), allow the first origin
        if (! $origin) {
            return $allowedOrigins[0];
        }

        // Check if origin is in allowed list
        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // For development, allow any localhost origin
        if (app()->environment('local') && (
            str_starts_with($origin, 'http://localhost:') ||
            str_starts_with($origin, 'http://127.0.0.1:')
        )) {
            return $origin;
        }

        return $allowedOrigins[0];
    }
}
