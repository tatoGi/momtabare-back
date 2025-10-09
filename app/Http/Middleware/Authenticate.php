<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        // Skip redirect if the current route is login
        if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit')) {
            return $next($request);
        }

        try {
            return parent::handle($request, $next, ...$guards);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return redirect()->route('admin.login', ['locale' => app()->getLocale()]);
        }
    }
}
