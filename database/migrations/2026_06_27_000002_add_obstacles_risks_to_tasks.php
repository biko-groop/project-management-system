<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'obstacles')) {
                $table->text('obstacles')->nullable()->after('delay_needs_decision'); // المعوقات
            }
            if (! Schema::hasColumn('tasks', 'potential_risks')) {
                $table->text('potential_risks')->nullable()->after('obstacles'); // المخاطر المحتملة
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['obstacles', 'potential_risks']);
        });
    }
};
