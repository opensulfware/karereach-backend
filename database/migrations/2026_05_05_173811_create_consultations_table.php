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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The CHW who created it
            $table->integer('patient_age');
            $table->enum('patient_sex', ['male', 'female', 'other']);
            $table->text('chief_complaint');
            $table->integer('duration_days')->default(1);
            $table->json('symptoms'); // Multi-select symptoms
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
