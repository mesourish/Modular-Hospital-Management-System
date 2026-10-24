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
        $allowed = ['hi', 'mr'];
        $locale = $request->get('lang') ?? session('app_locale', 'hi');

        if (! in_array($locale, $allowed, true)) {
            $locale = 'hi';
        }

        session(['app_locale' => $locale]);
        App::setLocale($locale);

        return $next($request);
    }
}
