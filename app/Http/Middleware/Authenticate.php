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
        // Skip authentication for login/logout routes
        if (
            $request->routeIs('admin.login') ||
            $request->routeIs('admin.login.submit') ||
            $request->routeIs('admin.logout')
        ) {
            return $next($request);
        }
    
        $guards = empty($guards) ? ['web'] : $guards;
    
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }
    
        return redirect()->route('admin.dashboard', ['locale' => $request->route('locale') ?? app()->getLocale()]);

    }
    
}
