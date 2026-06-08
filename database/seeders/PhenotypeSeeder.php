<?php

namespace Database\Seeders;

use App\Models\Phenotype;
use Database\Factories\PhenotypeFactory;
use Illuminate\Database\Seeder;

/**
 * Seed Data_Fenotipe awal (Req 13.1, 2.2).
 *
 * Menanam nilai realistis per kategori fenotipe sebagai satu-satunya sumber
 * nilai valid pada form prediksi Tahap 2 dan validasi Data_Latih.
 */
class PhenotypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PhenotypeFactory::values() as $category => $values) {
            foreach ($values as $value) {
                Phenotype::query()->firstOrCreate([
                    'category' => $category,
                    'value' => $value,
                ]);
            }
        }
    }
}
