<?php

namespace App\Domain;

/**
 * Kategori Fenotipe yang diamati pada orang tua dan diprediksi untuk bayi.
 *
 * Backing value mengikuti string kategori yang disimpan pada kolom
 * `phenotypes.category` dan dipakai sebagai kunci pada Data_Fenotipe
 * serta hasil prediksi fisik (Req 2.1, 4.1).
 */
enum PhenotypeCategory: string
{
    case GolonganDarah = 'Golongan Darah';
    case WarnaIris = 'Warna Iris Mata';
    case TeksturRambut = 'Tekstur Rambut';
    case BentukCuping = 'Bentuk Cuping Telinga';
}
