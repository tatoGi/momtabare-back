<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RetailerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
      
        $token = $request->bearerToken();
        $tokenModel = $token ? \Laravel\Sanctum\PersonalAccessToken::findToken(explode('|', $token)[1] ?? '') : null;
        $user = $tokenModel ? $tokenModel->tokenable : null;
      
        if (! $user) {
            // For web requests, redirect to login
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
    
            return redirect()->route('login', [app()->getLocale()]);
        }
    
        // Check if user is a retailer
        if (! $user->is_retailer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Retailer account required.',
                ], 403);
            }
    
            return redirect()->route('home', [app()->getLocale()])
                ->with('error', 'Access denied. Retailer account required.');
        }
    
        return $next($request);
    }
}
