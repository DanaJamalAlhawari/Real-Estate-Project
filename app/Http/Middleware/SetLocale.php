<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');
        if ($request->isMethod('GET') && in_array($locale, ['ar', 'en'], true)) {
            $request->session()->put('locale', $locale);

            return redirect()->to($request->fullUrlWithoutQuery('lang'));
        }

        app()->setLocale($request->session()->get('locale', config('app.locale')));

        return $next($request);
    }
}
