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
        // Skip authentication for login routes and logout
        if (
            $request->routeIs('admin.login') ||
            $request->routeIs('admin.login.submit') ||
            $request->routeIs('admin.logout')
        ) {
            return $next($request);
        }

        if (empty($guards)) {
            $guards = ['web'];
        }

        foreach ($guards as $guard) {

            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        // 👇 Redirect to the login page (NOT dashboard)
        return redirect('/'.app()->getLocale().'/admin/login');
    }
}
