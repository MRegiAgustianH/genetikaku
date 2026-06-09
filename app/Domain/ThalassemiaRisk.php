<?php

namespace App\Domain;

enum ThalassemiaRisk: string
{
    case Rendah = 'Rendah';
    case Sedang = 'Sedang';
    case Tinggi = 'Tinggi';
}
