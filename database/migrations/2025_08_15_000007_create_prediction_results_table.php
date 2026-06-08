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
        Schema::create('prediction_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_result_id')
                ->constrained('screening_results')
                ->cascadeOnDelete();
            $table->json('physical_result');
            $table->string('thalassemia_risk'); // Rendah|Sedang|Tinggi
            $table->json('probabilities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_results');
    }
};
