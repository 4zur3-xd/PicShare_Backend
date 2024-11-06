<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AppLocalization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get language from logged in user
        if (Auth::check() && Auth::user()->language) {
            $locale = Auth::user()->language;
        }
        // get from header
        elseif ($request->hasHeader('App-Language')) {
            $locale = $request->header('App-Language');
        }
        elseif (env('APP_LOCALE') != null) {
            $locale = env('APP_LOCALE');
        } else {
            $locale = 'en';
        }

        // Set language for Laravel
        App::setLocale($locale);

        // Continue processing request
        return $next($request);
    }
}
