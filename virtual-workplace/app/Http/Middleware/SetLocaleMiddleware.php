<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request to set the application locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check direct query parameter (?lang=ar or ?lang=en)
        if ($request->has('lang') && in_array($request->query('lang'), ['ar', 'en'])) {
            $locale = $request->query('lang');
            Session::put('locale', $locale);
            Cookie::queue('locale', $locale, 525600); // 1 year
        }
        // 2. Check session
        elseif (Session::has('locale') && in_array(Session::get('locale'), ['ar', 'en'])) {
            $locale = Session::get('locale');
        }
        // 3. Check cookie
        elseif ($request->hasCookie('locale') && in_array($request->cookie('locale'), ['ar', 'en'])) {
            $locale = $request->cookie('locale');
            Session::put('locale', $locale);
        }
        // 4. Default
        else {
            $locale = config('app.locale', 'ar'); // Default to Arabic if desired or 'ar'
        }

        App::setLocale($locale);

        return $next($request);
    }
}
