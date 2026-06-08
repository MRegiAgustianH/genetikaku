<?php

namespace App\Domain;

/**
 * Hasil_Skrining_Orang_Tua: klasifikasi risiko Thalassemia satu orang tua.
 *
 * Backing value mengikuti string yang disimpan pada kolom
 * `screening_results.father_result` / `mother_result` (Req 1.3, 1.5).
 */
enum ScreeningCategory: string
{
    case Normal = 'Normal';
    case Carrier = 'Carrier';
    case BerisikoTinggi = 'Berisiko Tinggi';
}
