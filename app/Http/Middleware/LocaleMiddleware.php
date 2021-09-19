<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $language = $request->server('HTTP_APP_LANGUAGE');
        if (isset($language) && array_key_exists($language, config('locale.languages'))) {
            // Set the Laravel locale
            App::setLocale($language);
        }
        return $next($request);
    }
}
