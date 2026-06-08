<?php

namespace Database\Seeders;

use App\Models\TrainingData;
use Illuminate\Database\Seeder;

/**
 * Seed Data_Latih awal untuk Mesin_Naive_Bayes (Req 14.1, 3.2).
 *
 * Semua nilai atribut diambil dari Data_Fenotipe (lihat PhenotypeFactory::values)
 * dan kategori Hasil_Skrining_Orang_Tua yang valid (Normal/Carrier/Berisiko Tinggi),
 * sehingga konsisten dengan Data_Fenotipe (Req 14.3).
 */
class TrainingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Baris deterministik representatif yang mencakup ketiga kelas risiko.
        $rows = [
            [
                'father_blood' => 'A', 'father_iris' => 'Cokelat', 'father_hair' => 'Lurus', 'father_ear' => 'Terpisah', 'father_thalassemia' => 'Normal',
                'mother_blood' => 'O', 'mother_iris' => 'Hitam', 'mother_hair' => 'Lurus', 'mother_ear' => 'Terpisah', 'mother_thalassemia' => 'Normal',
                'baby_blood' => 'A', 'baby_iris' => 'Cokelat', 'baby_hair' => 'Lurus', 'baby_ear' => 'Terpisah', 'baby_thalassemia_risk' => 'Rendah',
            ],
            [
                'father_blood' => 'B', 'father_iris' => 'Hitam', 'father_hair' => 'Bergelombang', 'father_ear' => 'Melekat', 'father_thalassemia' => 'Carrier',
                'mother_blood' => 'A', 'mother_iris' => 'Cokelat', 'mother_hair' => 'Bergelombang', 'mother_ear' => 'Terpisah', 'mother_thalassemia' => 'Normal',
                'baby_blood' => 'AB', 'baby_iris' => 'Cokelat', 'baby_hair' => 'Bergelombang', 'baby_ear' => 'Terpisah', 'baby_thalassemia_risk' => 'Sedang',
            ],
            [
                'father_blood' => 'AB', 'father_iris' => 'Biru', 'father_hair' => 'Keriting', 'father_ear' => 'Melekat', 'father_thalassemia' => 'Berisiko Tinggi',
                'mother_blood' => 'B', 'mother_iris' => 'Hijau', 'mother_hair' => 'Keriting', 'mother_ear' => 'Melekat', 'mother_thalassemia' => 'Carrier',
                'baby_blood' => 'B', 'baby_iris' => 'Hijau', 'baby_hair' => 'Keriting', 'baby_ear' => 'Melekat', 'baby_thalassemia_risk' => 'Tinggi',
            ],
            [
                'father_blood' => 'O', 'father_iris' => 'Cokelat', 'father_hair' => 'Lurus', 'father_ear' => 'Terpisah', 'father_thalassemia' => 'Normal',
                'mother_blood' => 'O', 'mother_iris' => 'Cokelat', 'mother_hair' => 'Bergelombang', 'mother_ear' => 'Melekat', 'mother_thalassemia' => 'Carrier',
                'baby_blood' => 'O', 'baby_iris' => 'Cokelat', 'baby_hair' => 'Lurus', 'baby_ear' => 'Terpisah', 'baby_thalassemia_risk' => 'Sedang',
            ],
            [
                'father_blood' => 'A', 'father_iris' => 'Hitam', 'father_hair' => 'Bergelombang', 'father_ear' => 'Terpisah', 'father_thalassemia' => 'Carrier',
                'mother_blood' => 'B', 'mother_iris' => 'Hitam', 'mother_hair' => 'Lurus', 'mother_ear' => 'Terpisah', 'mother_thalassemia' => 'Carrier',
                'baby_blood' => 'AB', 'baby_iris' => 'Hitam', 'baby_hair' => 'Bergelombang', 'baby_ear' => 'Terpisah', 'baby_thalassemia_risk' => 'Tinggi',
            ],
            [
                'father_blood' => 'B', 'father_iris' => 'Cokelat', 'father_hair' => 'Lurus', 'father_ear' => 'Melekat', 'father_thalassemia' => 'Normal',
                'mother_blood' => 'AB', 'mother_iris' => 'Biru', 'mother_hair' => 'Bergelombang', 'mother_ear' => 'Terpisah', 'mother_thalassemia' => 'Normal',
                'baby_blood' => 'B', 'baby_iris' => 'Cokelat', 'baby_hair' => 'Bergelombang', 'baby_ear' => 'Terpisah', 'baby_thalassemia_risk' => 'Rendah',
            ],
            [
                'father_blood' => 'O', 'father_iris' => 'Hijau', 'father_hair' => 'Keriting', 'father_ear' => 'Melekat', 'father_thalassemia' => 'Berisiko Tinggi',
                'mother_blood' => 'A', 'mother_iris' => 'Hitam', 'mother_hair' => 'Keriting', 'mother_ear' => 'Melekat', 'mother_thalassemia' => 'Berisiko Tinggi',
                'baby_blood' => 'A', 'baby_iris' => 'Hitam', 'baby_hair' => 'Keriting', 'baby_ear' => 'Melekat', 'baby_thalassemia_risk' => 'Tinggi',
            ],
            [
                'father_blood' => 'A', 'father_iris' => 'Cokelat', 'father_hair' => 'Lurus', 'father_ear' => 'Terpisah', 'father_thalassemia' => 'Normal',
                'mother_blood' => 'A', 'mother_iris' => 'Cokelat', 'mother_hair' => 'Lurus', 'mother_ear' => 'Terpisah', 'mother_thalassemia' => 'Normal',
                'baby_blood' => 'A', 'baby_iris' => 'Cokelat', 'baby_hair' => 'Lurus', 'baby_ear' => 'Terpisah', 'baby_thalassemia_risk' => 'Rendah',
            ],
        ];

        foreach ($rows as $row) {
            TrainingData::query()->create($row);
        }

        // Tambahan baris acak yang tetap konsisten dengan Data_Fenotipe.
        TrainingData::factory()->count(12)->create();
    }
}
