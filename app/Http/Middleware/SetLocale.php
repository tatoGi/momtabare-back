<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Skip locale handling for admin routes
        if ($request->is('admin*')) {
            return $next($request);
        }

        // Get the first segment as locale
        $locale = $request->segment(1);
       
        // Get available locales from config
        $availableLocales = config('app.locales', []);

        // If no locale is set in the URL, use the default from session or config
        if (!in_array($locale, $availableLocales)) {
            $locale = session('locale', config('app.locale'));
            $segments = $request->segments();
            
            // Only prepend locale if it's not already there
            if (!in_array($locale, $segments)) {
                array_unshift($segments, $locale);
                return redirect()->to(implode('/', $segments));
            }
        }

        // Set the application locale
        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Remove the locale parameter from the route parameters
        $request->route()->forgetParameter('locale');
        
        return $next($request);
    }
}