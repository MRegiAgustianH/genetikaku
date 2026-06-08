<?php

namespace App\Domain;

/**
 * DTO satu aturan Basis_Pengetahuan skrining Thalassemia.
 *
 * Memetakan satu Indikator_Skrining ke bobot dan pemetaan klasifikasinya,
 * digunakan Mesin_Skrining untuk menghitung Hasil_Skrining_Orang_Tua
 * (Req 12.1, 12.2). Mengikuti ERD tabel `knowledge_base_rules`.
 */
final readonly class KnowledgeBaseRule
{
    public function __construct(
        public string $indicator,
        public int $weight,
        public string $classificationMapping,
    ) {}

    /**
     * Buat KnowledgeBaseRule dari array asosiatif (mis. baris Eloquent/array DB).
     *
     * @param  array<string,mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            indicator: (string) $attributes['indicator'],
            weight: (int) $attributes['weight'],
            classificationMapping: (string) $attributes['classification_mapping'],
        );
    }
}
