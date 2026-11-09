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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caregiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('gender')->nullable(); // male | female | other
            $table->string('phone')->nullable();
            $table->string('diabetes_type')->default('type2'); // type1 | type2 | other
            $table->integer('target_low')->default(80);
            $table->integer('target_high')->default(180);
            $table->decimal('usual_morning_dose', 5, 1)->nullable();
            $table->decimal('usual_night_dose', 5, 1)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
