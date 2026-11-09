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
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('log_date');
            $table->unsignedSmallInteger('nashta_sugar')->nullable();
            $table->string('nashta_timing')->default('before')->nullable(); // before | after
            $table->decimal('insulin_morning', 5, 1)->nullable();
            $table->unsignedSmallInteger('lunch_sugar')->nullable();
            $table->string('lunch_timing')->nullable(); // before | after
            $table->unsignedSmallInteger('dinner_sugar')->nullable();
            $table->string('dinner_timing')->default('before')->nullable(); // before | after
            $table->decimal('insulin_night', 5, 1)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('needs_check')->default(false);
            $table->timestamps();

            $table->unique(['patient_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
