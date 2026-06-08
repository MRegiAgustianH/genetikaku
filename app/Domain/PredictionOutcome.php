<?php

namespace App\Domain;

/**
 * DTO hasil prediksi Mesin_Naive_Bayes (Tahap 3–4).
 *
 * - $physical: prediksi karakteristik fisik bayi, map kategori fenotipe => nilai
 *   (Golongan Darah, Warna Iris Mata, Tekstur Rambut, Bentuk Cuping Telinga) (Req 4.1).
 * - $thalassemiaRisk: Risiko_Thalassemia_Bayi terpilih (Req 4.2).
 * - $probabilities: probabilitas posterior ternormalisasi per variabel keluaran,
 *   map variabel => (map kelas => float) (Req 4.3).
 *
 * @phpstan-type ProbabilityMap array<string,array<string,float>>
 */
final readonly class PredictionOutcome
{
    /**
     * @param  array<string,string>  $physical
     * @param  array<string,array<string,float>>  $probabilities
     */
    public function __construct(
        public array $physical,
        public ThalassemiaRisk $thalassemiaRisk,
        public array $probabilities,
    ) {}
}
