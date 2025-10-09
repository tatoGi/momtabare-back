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
        // Skip authentication for login routes and login submission
        if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit') || $request->routeIs('admin.logout')) {
            return $next($request);
        }

        // If no specific guard is specified, use the default web guard
        if (empty($guards)) {
            $guards = ['web'];
        }

        // Check if user is authenticated via any of the specified guards
        foreach ($guards as $guard) {
            
            if (Auth::guard($guard)->check()) {
                // User is authenticated, continue to the next middleware
                return $next($request);
            }
        }

        // If we get here, the user is not authenticated
        return redirect()->route('admin.login', ['locale' => app()->getLocale()]);
    }
}
