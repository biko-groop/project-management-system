<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', app()->getLocale() == 'ar' ? 'يجب تسجيل الدخول أولاً' : 'You must login first');
        }

        $user = Auth::user();
        
        // التحقق من أن الحساب نشط
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', app()->getLocale() == 'ar' ? 'حسابك غير نشط، يرجى الاتصال بالمدير' : 'Your account is inactive, please contact the administrator');
        }

        return $next($request);
    }
}