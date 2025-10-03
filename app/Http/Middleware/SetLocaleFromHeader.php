<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get available locales from config
        $availableLocales = config('app.locales', []);

        // Get locale from Accept-Language header
        $locale = $this->getLocaleFromHeader($request, $availableLocales);

        // Set the application locale
        app()->setLocale($locale);

        // Store locale in session for consistency
        session(['locale' => $locale]);

        return $next($request);
    }

    /**
     * Extract locale from Accept-Language header
     */
    private function getLocaleFromHeader(Request $request, array $availableLocales): string
    {
        // Get Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return config('app.locale', 'en');
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,ka;q=0.8")
        $languages = [];
        $parts = explode(',', $acceptLanguage);

        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ';') !== false) {
                [$lang, $q] = explode(';', $part, 2);
                $q = floatval(str_replace('q=', '', $q));
            } else {
                $lang = $part;
                $q = 1.0;
            }

            // Extract language code (e.g., "en" from "en-US")
            $langCode = explode('-', trim($lang))[0];
            $languages[$langCode] = $q;
        }

        // Sort by quality value (highest first)
        arsort($languages);

        // Find the first supported language
        foreach (array_keys($languages) as $langCode) {
            if (in_array($langCode, $availableLocales)) {
                return $langCode;
            }
        }

        // Fallback to default locale if no supported language found
        return config('app.locale', 'en');
    }
}
