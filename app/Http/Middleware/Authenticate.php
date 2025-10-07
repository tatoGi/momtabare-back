<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Skip authentication for login routes
        if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit')) {
            return $next($request);
        }
    
        // Default to web guard if none specified
        if (empty($guards)) {
            $guards = ['web'];
        }
    
        try {
            // Get the authenticated user first
            $user = $request->user();
            
            // For admin routes, check if user is authenticated and is admin
            if ($request->is('admin/*')) {
                if (!$user) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect(route('admin.login', ['locale' => app()->getLocale()]));
                }
                
                // Additional admin check if you have is_admin column
                if (property_exists($user, 'is_admin') && !$user->is_admin) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect(route('admin.login', ['locale' => app()->getLocale()]));
                }
            }
    
            // If we got here, proceed with the request
            return $next($request);
    
        } catch (\Exception $e) {
            Log::error('Authentication Error: ' . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);
            
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
    
            return redirect(route('admin.login', ['locale' => app()->getLocale()]));
        }
    }
}