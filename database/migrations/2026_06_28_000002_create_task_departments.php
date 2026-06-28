<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_departments')) {
            Schema::create('task_departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                // نوع مسؤولية القسم تجاه المهمة: رئيسي/مالي/استشاري/موافقة/تنفيذ/دعم
                $table->string('responsibility')->default('primary');
                $table->text('note')->nullable();
                $table->timestamps();
                $table->unique(['task_id', 'department_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_departments');
    }
};
