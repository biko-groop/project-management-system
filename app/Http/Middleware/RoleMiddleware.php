<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // إذا لم يتم تحديد أدوار، السماح بالوصول لجميع المستخدمين النشطين
        if (empty($roles)) {
            if (!$user->is_active) {
                return redirect()->route('dashboard')
                    ->with('error', app()->getLocale() == 'ar' ? 'حسابك غير نشط' : 'Your account is inactive');
            }
            return $next($request);
        }

        // التحقق من صلاحية المستخدم
        foreach ($roles as $role) {
            if ($user->role === $role) {
                if (!$user->is_active) {
                    return redirect()->route('dashboard')
                        ->with('error', app()->getLocale() == 'ar' ? 'حسابك غير نشط' : 'Your account is inactive');
                }
                return $next($request);
            }
        }

        // إذا لم تكن لديه الصلاحية
        return redirect()->route('dashboard')
            ->with('error', app()->getLocale() == 'ar' ? 'ليس لديك صلاحية للوصول لهذه الصفحة' : 'You do not have permission to access this page');
    }
}