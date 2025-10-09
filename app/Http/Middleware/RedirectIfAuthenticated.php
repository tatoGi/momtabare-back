<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
      
        $guards = empty($guards) ? [null] : $guards;
        dd($guards);
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
             
                // If trying to access admin login while already authenticated, redirect to admin dashboard
                if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit')) {
                    return redirect()->route('admin.dashboard', ['locale' => app()->getLocale()]);
                }
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}