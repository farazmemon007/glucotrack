<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_logs', 'insulin_lunch')) {
                $table->decimal('insulin_lunch', 5, 1)->nullable()->after('lunch_time_after');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            if (Schema::hasColumn('daily_logs', 'insulin_lunch')) {
                $table->dropColumn('insulin_lunch');
            }
        });
    }
};
