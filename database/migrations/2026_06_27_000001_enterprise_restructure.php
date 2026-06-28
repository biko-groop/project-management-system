<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== الأقسام =====
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // ===== توسعة المستخدم =====
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) $table->string('phone')->nullable()->after('email');
            if (! Schema::hasColumn('users', 'job_title')) $table->string('job_title')->nullable()->after('phone');
            if (! Schema::hasColumn('users', 'department_id')) $table->foreignId('department_id')->nullable()->after('job_title')->constrained('departments')->nullOnDelete();
            if (! Schema::hasColumn('users', 'manager_id')) $table->foreignId('manager_id')->nullable()->after('department_id')->constrained('users')->nullOnDelete();
            if (! Schema::hasColumn('users', 'avatar')) $table->string('avatar')->nullable()->after('manager_id');
        });

        // ===== توسعة المشروع =====
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'priority')) $table->string('priority')->default('medium')->after('status');
            if (! Schema::hasColumn('projects', 'progress')) $table->unsignedTinyInteger('progress')->default(0)->after('priority');
            if (! Schema::hasColumn('projects', 'manager_id')) $table->foreignId('manager_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        // ===== الأقسام المشاركة في المشروع =====
        if (! Schema::hasTable('department_project')) {
            Schema::create('department_project', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['project_id', 'department_id']);
            });
        }

        // ===== توسعة المهمة =====
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'department_id')) $table->foreignId('department_id')->nullable()->after('project_id')->constrained('departments')->nullOnDelete();
            if (! Schema::hasColumn('tasks', 'progress')) $table->unsignedTinyInteger('progress')->default(0)->after('priority');
            if (! Schema::hasColumn('tasks', 'estimated_hours')) $table->decimal('estimated_hours', 6, 2)->nullable()->after('due_date');
            if (! Schema::hasColumn('tasks', 'actual_hours')) $table->decimal('actual_hours', 6, 2)->nullable()->after('estimated_hours');
            // أسباب التأخير
            if (! Schema::hasColumn('tasks', 'delay_reason')) $table->text('delay_reason')->nullable()->after('actual_hours');
            if (! Schema::hasColumn('tasks', 'delay_needs_support')) $table->boolean('delay_needs_support')->default(false)->after('delay_reason');
            if (! Schema::hasColumn('tasks', 'delay_needs_approval')) $table->boolean('delay_needs_approval')->default(false)->after('delay_needs_support');
            if (! Schema::hasColumn('tasks', 'delay_needs_budget')) $table->boolean('delay_needs_budget')->default(false)->after('delay_needs_approval');
            if (! Schema::hasColumn('tasks', 'delay_needs_decision')) $table->boolean('delay_needs_decision')->default(false)->after('delay_needs_budget');
        });

        // ===== معوقات المهمة =====
        if (! Schema::hasTable('task_obstacles')) {
            Schema::create('task_obstacles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->date('occurred_on')->nullable();
                $table->string('type')->nullable();
                $table->text('description');
                $table->string('impact')->default('medium'); // low, medium, high
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('open'); // open, in_progress, resolved, closed
                $table->timestamps();
            });
        }

        // ===== تعليقات المهمة =====
        if (! Schema::hasTable('task_comments')) {
            Schema::create('task_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        // ===== السجل الزمني للمهمة =====
        if (! Schema::hasTable('task_activities')) {
            Schema::create('task_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event'); // created, updated, status_changed, assigned, commented, obstacle_added, completed ...
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activities');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_obstacles');
        Schema::dropIfExists('department_project');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'progress', 'estimated_hours', 'actual_hours', 'delay_reason', 'delay_needs_support', 'delay_needs_approval', 'delay_needs_budget', 'delay_needs_decision']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['priority', 'progress', 'manager_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'job_title', 'department_id', 'manager_id', 'avatar']);
        });
        Schema::dropIfExists('departments');
    }
};
