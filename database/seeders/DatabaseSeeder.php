<?php

namespace Database\Seeders;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Contoh user admin (Req 9.x) untuk mengakses area administrasi.
        User::query()->updateOrCreate(
            ['email' => 'admin@genetikaku.test'],
            [
                'name' => 'Admin GENETIKAKU',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );

        // Contoh user publik biasa.
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ],
        );

        $this->call([
            PhenotypeSeeder::class,
            TrainingDataSeeder::class,
            ArticleSeeder::class,
        ]);

        // Baris placeholder untuk ilustrasi halaman skrining/prediksi. Path
        // dibiarkan null agar admin dapat mengunggah media kapan saja.
        MediaAsset::query()->updateOrCreate(
            ['key' => 'screening_illustration'],
            ['type' => 'image'],
        );
    }
}
