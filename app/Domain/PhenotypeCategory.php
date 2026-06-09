<?php

namespace App\Domain;


enum PhenotypeCategory: string
{
    case GolonganDarah = 'Golongan Darah';
    case WarnaIris = 'Warna Iris Mata';
    case TeksturRambut = 'Tekstur Rambut';
    case BentukCuping = 'Bentuk Cuping Telinga';
}
