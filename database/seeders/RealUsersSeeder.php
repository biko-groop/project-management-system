<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1) تنظيف بيانات التجربة (مع الإبقاء على الأقسام والأدمن)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'task_activities', 'task_comments', 'task_obstacles', 'task_dependencies',
            'task_departments', 'task_files', 'project_files', 'project_team',
            'team_members', 'project_members', 'tasks', 'projects', 'teams',
            'notifications', 'audit_logs',
        ] as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // حذف كل المستخدمين عدا الأدمن
        User::where('email', '!=', 'admin@qurtuba.test')->delete();

        // 2) خريطة الأقسام (بالاسم)
        $dept = Department::pluck('id', 'name');
        $it = $dept['تقنية المعلومات'] ?? null;
        $fin = $dept['المالية'] ?? null;
        $hr = $dept['الموارد البشرية'] ?? null;
        $pmo = $dept['المشاريع'] ?? null;

        // 3) الموظفون الحقيقيون
        $users = [
            ['name' => 'إبراهيم الرشيد', 'email' => 'i.alrasheed@gheras.sch.sa', 'job_title' => 'مدير المشاريع', 'role' => 'manager', 'department_id' => $pmo],
            ['name' => 'مشاري الدخيل', 'email' => 'gss.office@gheras.sch.sa', 'job_title' => 'المشرف العام', 'role' => 'manager', 'department_id' => null],
            ['name' => 'سعيد قطب', 'email' => 'HRM@gheras.sch.sa', 'job_title' => 'المدير المالي', 'role' => 'manager', 'department_id' => $fin],
            ['name' => 'وليد القحطاني', 'email' => 'CFO@gheras.sch.sa', 'job_title' => 'مدير الموارد البشرية', 'role' => 'manager', 'department_id' => $hr],
            ['name' => 'طارق حامد', 'email' => 'tarek.h@gheras.sch.sa', 'job_title' => 'مدير الخدمات المساندة', 'role' => 'manager', 'department_id' => null],
            ['name' => 'ابوبكر حسن', 'email' => 'ITM@gheras.sch.sa', 'job_title' => 'مسؤول تقنية المعلومات', 'role' => 'manager', 'department_id' => $it],
            ['name' => 'ياسر السيد', 'email' => 'y.alsaeed@gheras.sch.sa', 'job_title' => 'مهندس الكهرباء', 'role' => 'user', 'department_id' => $it],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                array_merge($u, [
                    'password' => Hash::make('123456'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );
        }
    }
}
