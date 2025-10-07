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
    
        try {
            // Call parent's handle method first to handle authentication
            return parent::handle($request, $next, ...$guards);
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