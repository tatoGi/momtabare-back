<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSession
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure session is started
        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            $request->session()->start();
        }

        // Share session data with all views
        view()->share('session_data', $request->session()->all());

        return $next($request);
    }
}
