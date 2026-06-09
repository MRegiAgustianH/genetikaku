<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;


class AboutSeeder extends Seeder
{
    public function run(): void
    {
        $content = <<<'TEXT'
GENETIKAKU adalah sistem pakar berbasis web yang mengimplementasikan metode Naive Bayes untuk membantu calon orang tua melakukan deteksi dini secara mandiri terhadap risiko penyakit Thalassemia pada bayi, sekaligus memprediksi karakteristik fisiknya.

Latar Belakang

Thalassemia merupakan kelainan darah genetik yang diturunkan dari orang tua kepada anak. Keterbatasan akses terhadap pakar genetika, khususnya di Desa Nagrak, Kabupaten Cianjur, mendorong dibangunnya media asisten virtual yang mudah diakses dan efisien sebagai alat bantu edukasi kesehatan dini.

Maksud

Membangun sistem pakar berbasis web dengan implementasi metode Naive Bayes untuk memberikan prediksi karakteristik fisik dan analisis risiko penyakit Thalassemia pada bayi, guna membantu calon orang tua melakukan deteksi dini secara mandiri.

Tujuan

- Mengimplementasikan algoritma Naive Bayes untuk klasifikasi probabilitas karakteristik fisik dan risiko Thalassemia berdasarkan data fenotipe orang tua secara akurat.
- Menyediakan media asisten virtual yang mudah diakses dan efisien bagi masyarakat guna mengatasi keterbatasan akses terhadap pakar genetika.
- Menghasilkan output prediksi yang objektif sebagai alat bantu edukasi kesehatan dini dalam menekan angka kelahiran penderita Thalassemia.

Cara Kerja Singkat

1. Skrining risiko Thalassemia kedua orang tua melalui indikator sederhana.
2. Input data fenotipe (golongan darah, warna iris, tekstur rambut, bentuk cuping) ayah dan ibu.
3. Perhitungan Naive Bayes terhadap data latih untuk memprediksi karakteristik fisik dan risiko Thalassemia bayi.
4. Penyajian hasil disertai probabilitas, edukasi, dan penyangkalan.

GENETIKAKU bersifat skrining dan edukasi awal, bukan alat diagnosis medis, dan tidak menggantikan pemeriksaan laboratorium maupun konsultasi tenaga kesehatan.
TEXT;

        $about = AboutPage::query()->first();

        AboutPage::query()->updateOrCreate(
            ['id' => $about?->id],
            [
                'title' => 'Tentang GENETIKAKU',
                'content' => $content,
            ],
        );
    }
}
