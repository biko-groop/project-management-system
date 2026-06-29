<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| النظام بالكامل يعمل عبر لوحة Filament الحديثة على /admin
| (الواجهة القديمة Bootstrap حُذفت بالكامل).
*/

Route::get('/', fn () => redirect('/admin'));

// طباعة وتصدير التقارير (تتطلب جلسة مسجّلة)
Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
