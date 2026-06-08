<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel media_assets menyimpan aset media yang dikelola Admin (mis.
     * ilustrasi halaman skrining). Diidentifikasi oleh `key` unik sehingga
     * frontend dapat memuat aset tertentu secara konsisten. `path` nullable
     * agar baris placeholder dapat dibuat sebelum berkas diunggah.
     */
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('path')->nullable();
            $table->string('type')->default('image');
            $table->string('alt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
