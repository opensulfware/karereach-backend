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
        Schema::create('ai_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->unique()->constrained()->onDelete('cascade');
            $table->json('probable_conditions');
            $table->enum('risk_level', ['low', 'urgent', 'emergency']);
            $table->enum('next_action', ['refer', 'treat', 'monitor']);
            $table->text('clinical_notes');
            $table->json('red_flags');
            $table->string('ollama_model')->nullable();
            $table->integer('ollama_response_time_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_results');
    }
};
