<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskDepartment;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\TaskObserver;
use App\Observers\TaskCommentObserver;
use App\Observers\TaskDepartmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تفعيل دعم Carbon للغة العربية
        Carbon::setLocale('ar');
        setlocale(LC_TIME, 'ar_SA.UTF-8', 'ar_SA', 'arabic');

        // تسجيل مراقب المهام (سجل زمني + إشعارات تلقائية)
        Task::observe(TaskObserver::class);
        TaskComment::observe(TaskCommentObserver::class);
        TaskDepartment::observe(TaskDepartmentObserver::class);

        // سجل التدقيق (Audit Log) للنماذج المهمة
        foreach ([Project::class, Task::class, User::class, Department::class] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
