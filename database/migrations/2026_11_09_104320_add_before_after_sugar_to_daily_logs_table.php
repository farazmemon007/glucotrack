<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('nashta_sugar_before')->nullable()->after('nashta_sugar');
            $table->unsignedSmallInteger('nashta_sugar_after')->nullable()->after('nashta_sugar_before');
            $table->unsignedSmallInteger('lunch_sugar_before')->nullable()->after('lunch_sugar');
            $table->unsignedSmallInteger('lunch_sugar_after')->nullable()->after('lunch_sugar_before');
            $table->unsignedSmallInteger('dinner_sugar_before')->nullable()->after('dinner_sugar');
            $table->unsignedSmallInteger('dinner_sugar_after')->nullable()->after('dinner_sugar_before');
        });

        // Migrate existing single readings into respective before/after columns
        DB::table('daily_logs')->whereNotNull('nashta_sugar')->get()->each(function ($log) {
            if ($log->nashta_timing === 'after') {
                DB::table('daily_logs')->where('id', $log->id)->update(['nashta_sugar_after' => $log->nashta_sugar]);
            } else {
                DB::table('daily_logs')->where('id', $log->id)->update(['nashta_sugar_before' => $log->nashta_sugar]);
            }
        });

        DB::table('daily_logs')->whereNotNull('lunch_sugar')->get()->each(function ($log) {
            if ($log->lunch_timing === 'after') {
                DB::table('daily_logs')->where('id', $log->id)->update(['lunch_sugar_after' => $log->lunch_sugar]);
            } else {
                DB::table('daily_logs')->where('id', $log->id)->update(['lunch_sugar_before' => $log->lunch_sugar]);
            }
        });

        DB::table('daily_logs')->whereNotNull('dinner_sugar')->get()->each(function ($log) {
            if ($log->dinner_timing === 'after') {
                DB::table('daily_logs')->where('id', $log->id)->update(['dinner_sugar_after' => $log->dinner_sugar]);
            } else {
                DB::table('daily_logs')->where('id', $log->id)->update(['dinner_sugar_before' => $log->dinner_sugar]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropColumn([
                'nashta_sugar_before',
                'nashta_sugar_after',
                'lunch_sugar_before',
                'lunch_sugar_after',
                'dinner_sugar_before',
                'dinner_sugar_after',
            ]);
        });
    }
};
