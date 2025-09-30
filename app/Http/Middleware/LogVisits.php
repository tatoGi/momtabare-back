<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class LogVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $location = Location::get($ip);

        Visit::create([
            'ip_address' => $ip,
            'country' => $location->countryName ?? 'Unknown',
            'city' => $location->cityName ?? 'Unknown',
        ]);

        return $next($request);
    }
}
