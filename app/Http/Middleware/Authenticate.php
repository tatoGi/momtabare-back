<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Skip authentication check for login routes
        if ($request->routeIs('admin.login.dashboard') || $request->routeIs('admin.login.submit')) {
            return $next($request);
        }
    
        if (empty($guards)) {
            $guards = [config('auth.defaults.guard')];
        }
    
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }
    
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    
        return redirect()->route('admin.login.dashboard', [app()->getLocale()]);
    }
}
