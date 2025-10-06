<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
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
        // Temporarily allow all requests for testing
        return $next($request);
    }
}