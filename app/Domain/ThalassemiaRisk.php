<?php

namespace App\Domain;

/**
 * Risiko_Thalassemia_Bayi: klasifikasi risiko Thalassemia pada bayi hasil prediksi.
 *
 * Backing value mengikuti string yang disimpan pada kolom
 * `prediction_results.thalassemia_risk` (Req 4.2).
 */
enum ThalassemiaRisk: string
{
    case Rendah = 'Rendah';
    case Sedang = 'Sedang';
    case Tinggi = 'Tinggi';
}
