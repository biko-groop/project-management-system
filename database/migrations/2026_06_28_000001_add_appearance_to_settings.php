<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'app_name')) {
                $table->string('app_name')->default('نظام إدارة المشاريع')->after('id');
            }
            if (! Schema::hasColumn('settings', 'primary_color')) {
                $table->string('primary_color')->default('indigo')->after('app_name');
            }
            if (! Schema::hasColumn('settings', 'sidebar_theme')) {
                $table->string('sidebar_theme')->default('light')->after('primary_color'); // light | dark
            }
        });

        // ضمان وجود صف إعدادات واحد
        if (DB::table('settings')->count() === 0) {
            DB::table('settings')->insert([
                'app_name' => 'نظام إدارة المشاريع',
                'primary_color' => 'indigo',
                'sidebar_theme' => 'light',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['app_name', 'primary_color', 'sidebar_theme']);
        });
    }
};
