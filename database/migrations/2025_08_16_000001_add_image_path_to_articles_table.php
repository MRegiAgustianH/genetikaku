<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambahkan kolom gambar opsional (nullable) pada artikel sehingga konten
     * lama tetap valid tanpa gambar.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
