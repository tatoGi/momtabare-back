<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string[]  ...$guards
     * @return mixed
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
    
        foreach ($guards as $guard) {
           
            if ($guard == 'web') {
             
                // Additional check for admin routes
                if ($request->is('admin/*') && !Auth::user()->is_admin) {
                   
                    Auth::logout();
                    return redirect()->route('admin.login', ['locale' => app()->getLocale()]);
                }
                return $next($request);
            }
        }
    
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    
        return redirect()->guest(route('admin.login', ['locale' => app()->getLocale()]));
    }
}