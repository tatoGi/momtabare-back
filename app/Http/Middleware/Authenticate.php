<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        // Skip login routes
        if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit')) {
            return $next($request);
        }

        // Get current session ID
        $sessionId = Session::getId();
       
        // Check if session exists in the database and is active
        $session = DB::table('sessions')->where('id', $sessionId)->first();

        if (!$session) {
            // No active session found → redirect to login
            return redirect()->route('admin.login', ['locale' => app()->getLocale()]);
        }

        // Optional: you can check if `user_id` exists
        if (!$session->user_id) {
            return redirect()->route('admin.login', ['locale' => app()->getLocale()]);
        }
        dd($session);
        // Session exists → allow request
        return $next($request);
    }
}
