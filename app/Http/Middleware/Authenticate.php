<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Skip authentication for login routes and logout
        if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit') || $request->routeIs('admin.logout')) {
            return $next($request);
        }

        // ✅ Simple Auth check
        if (Auth::check()) {
            return $next($request);
        }

        // ❌ Redirect to login if not authenticated
        return redirect('/' . app()->getLocale() . '/admin/login');
    }
}
