<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_keys(config('ridewrench.languages', []));
        $fallbackLocale = config('app.fallback_locale', 'en');

        $locale = $request->session()->get('locale');

        if (!$locale && $request->user()) {
            $locale = $request->user()->language;
        }

        if (!is_string($locale) || !in_array($locale, $availableLocales, true)) {
            $locale = $fallbackLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
