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
            if (!Schema::hasColumn('daily_logs', 'nashta_time')) {
                $table->time('nashta_time')->nullable()->after('nashta_timing');
            }
            if (!Schema::hasColumn('daily_logs', 'nashta_time_before')) {
                $table->time('nashta_time_before')->nullable()->after('nashta_time');
            }
            if (!Schema::hasColumn('daily_logs', 'nashta_time_after')) {
                $table->time('nashta_time_after')->nullable()->after('nashta_time_before');
            }

            if (!Schema::hasColumn('daily_logs', 'lunch_time')) {
                $table->time('lunch_time')->nullable()->after('lunch_timing');
            }
            if (!Schema::hasColumn('daily_logs', 'lunch_time_before')) {
                $table->time('lunch_time_before')->nullable()->after('lunch_time');
            }
            if (!Schema::hasColumn('daily_logs', 'lunch_time_after')) {
                $table->time('lunch_time_after')->nullable()->after('lunch_time_before');
            }

            if (!Schema::hasColumn('daily_logs', 'dinner_time')) {
                $table->time('dinner_time')->nullable()->after('dinner_timing');
            }
            if (!Schema::hasColumn('daily_logs', 'dinner_time_before')) {
                $table->time('dinner_time_before')->nullable()->after('dinner_time');
            }
            if (!Schema::hasColumn('daily_logs', 'dinner_time_after')) {
                $table->time('dinner_time_after')->nullable()->after('dinner_time_before');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $cols = [
                'nashta_time',
                'nashta_time_before',
                'nashta_time_after',
                'lunch_time',
                'lunch_time_before',
                'lunch_time_after',
                'dinner_time',
                'dinner_time_before',
                'dinner_time_after',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('daily_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
