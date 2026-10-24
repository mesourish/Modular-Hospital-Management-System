<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['en', 'hi', 'mr', 'bn'];
        $locale = $request->get('lang') ?? session('app_locale', 'en');

        if (in_array($locale, $allowed, true)) {
            session(['app_locale' => $locale]);
            App::setLocale($locale);
        }

        return $next($request);
    }
}
