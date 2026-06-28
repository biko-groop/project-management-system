<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        // التحقق من وجود معامل lang في URL
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            
            // التحقق من أن اللغة مدعومة
            if (in_array($locale, ['ar', 'en'])) {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        } 
        // أو استخدام اللغة المخزنة في الجلسة
        elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}