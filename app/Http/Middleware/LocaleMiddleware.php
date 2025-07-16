<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class LocaleMiddleware
{
    public function handle($request, Closure $next)
    {
        $locale = $request->header('locale') ?? 'ar';
        $supported = ['en', 'ar']; // Add more supported locales as needed
        if ($locale && in_array($locale, $supported)) {
            App::setLocale($locale);
        }
        return $next($request);
    }
}
