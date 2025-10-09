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
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = $guards ?: ['web'];

        // Allow access to login routes without redirect loop
        if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit')) {
            return $next($request);
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request); // User is authenticated
            }
        }

        // If request expects JSON (API)
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Otherwise, redirect to login with locale
        return redirect()->route('admin.login', ['locale' => app()->getLocale()]);
    }
}
