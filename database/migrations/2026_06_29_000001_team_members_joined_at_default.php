<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // السماح بإضافة عضو دون تحديد joined_at يدوياً (قيمة افتراضية تلقائية)
        DB::statement('ALTER TABLE `team_members` MODIFY `joined_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `team_members` MODIFY `joined_at` TIMESTAMP NOT NULL');
    }
};
