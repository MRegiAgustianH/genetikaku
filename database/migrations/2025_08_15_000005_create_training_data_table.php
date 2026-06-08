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
        Schema::create('training_data', function (Blueprint $table) {
            $table->id();

            // Atribut fenotipe + status thalassemia ayah
            $table->string('father_blood');
            $table->string('father_iris');
            $table->string('father_hair');
            $table->string('father_ear');
            $table->string('father_thalassemia');

            // Atribut fenotipe + status thalassemia ibu
            $table->string('mother_blood');
            $table->string('mother_iris');
            $table->string('mother_hair');
            $table->string('mother_ear');
            $table->string('mother_thalassemia');

            // Keluaran (kelas) bayi
            $table->string('baby_blood');
            $table->string('baby_iris');
            $table->string('baby_hair');
            $table->string('baby_ear');
            $table->string('baby_thalassemia_risk');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_data');
    }
};
