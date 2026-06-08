<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama publik GENETIKAKU.
     *
     * Mengirim penjelasan ringkas sistem, sorotan alur empat tahap,
     * dan pernyataan penyangkalan (disclaimer). Prop `disclaimerAvailable`
     * memberi sinyal ke halaman apakah disclaimer tersedia untuk dirender,
     * sehingga tautan masuk ke alur skrining hanya diaktifkan saat
     * disclaimer berhasil ditampilkan (Req 6.4).
     */
    public function index(): Response
    {
        $disclaimer = 'GENETIKAKU bersifat skrining dan edukasi awal, bukan alat diagnosis medis. '
            .'Hasil tidak menggantikan pemeriksaan laboratorium maupun konsultasi tenaga kesehatan. '
            .'Selalu konsultasikan kondisi Anda dengan dokter atau ahli genetika.';

        return Inertia::render('public/home', [
            // Penjelasan ringkas tentang GENETIKAKU (Req 6.1)
            'intro' => [
                'name' => 'GENETIKAKU',
                'tagline' => 'Skrining risiko Thalassemia & prediksi karakteristik bayi berbasis Naive Bayes.',
                'description' => 'GENETIKAKU membantu calon orang tua melakukan skrining risiko Thalassemia '
                    .'serta memperkirakan karakteristik fisik bayi berdasarkan data fenotipe kedua orang tua. '
                    .'Sistem memandu Anda melalui empat tahap: skrining risiko, input fenotipe, '
                    .'perhitungan Naive Bayes, dan penyajian hasil beserta edukasi.',
            ],
            // Sorotan alur empat tahap untuk konteks pengguna
            'highlights' => [
                [
                    'title' => 'Skrining Thalassemia',
                    'description' => 'Isi indikator skrining untuk ayah dan ibu guna memperoleh klasifikasi risiko.',
                ],
                [
                    'title' => 'Input Fenotipe',
                    'description' => 'Masukkan karakteristik fisik kedua orang tua sebagai dasar prediksi.',
                ],
                [
                    'title' => 'Perhitungan Naive Bayes',
                    'description' => 'Sistem menghitung prediksi berdasarkan data latih yang dikelola admin.',
                ],
                [
                    'title' => 'Hasil & Edukasi',
                    'description' => 'Lihat prediksi karakteristik bayi, risiko Thalassemia, dan penjelasan edukatif.',
                ],
            ],
            // Pernyataan penyangkalan (Req 6.3)
            'disclaimer' => $disclaimer,
            // Sinyal ketersediaan disclaimer untuk menggerbang tautan skrining (Req 6.4)
            'disclaimerAvailable' => $disclaimer !== '',
        ]);
    }
}
