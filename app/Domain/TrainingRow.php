<?php

namespace App\Domain;

final readonly class TrainingRow
{
    public function __construct(
        // Atribut masukan — ayah
        public string $fatherBlood,
        public string $fatherIris,
        public string $fatherHair,
        public string $fatherEar,
        public string $fatherThalassemia,
        // Atribut masukan — ibu
        public string $motherBlood,
        public string $motherIris,
        public string $motherHair,
        public string $motherEar,
        public string $motherThalassemia,
        // Variabel keluaran — bayi
        public string $babyBlood,
        public string $babyIris,
        public string $babyHair,
        public string $babyEar,
        public string $babyThalassemiaRisk,
    ) {}

    /**
     * Buat TrainingRow dari array asosiatif (mis. baris Eloquent/array DB).
     *
     * @param  array<string,string>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            fatherBlood: $attributes['father_blood'],
            fatherIris: $attributes['father_iris'],
            fatherHair: $attributes['father_hair'],
            fatherEar: $attributes['father_ear'],
            fatherThalassemia: $attributes['father_thalassemia'],
            motherBlood: $attributes['mother_blood'],
            motherIris: $attributes['mother_iris'],
            motherHair: $attributes['mother_hair'],
            motherEar: $attributes['mother_ear'],
            motherThalassemia: $attributes['mother_thalassemia'],
            babyBlood: $attributes['baby_blood'],
            babyIris: $attributes['baby_iris'],
            babyHair: $attributes['baby_hair'],
            babyEar: $attributes['baby_ear'],
            babyThalassemiaRisk: $attributes['baby_thalassemia_risk'],
        );
    }

    /**
     * Atribut masukan (fitur) baris ini sebagai map kunci=>nilai.
     *
     * @return array<string,string>
     */
    public function inputAttributes(): array
    {
        return [
            'father_blood' => $this->fatherBlood,
            'father_iris' => $this->fatherIris,
            'father_hair' => $this->fatherHair,
            'father_ear' => $this->fatherEar,
            'father_thalassemia' => $this->fatherThalassemia,
            'mother_blood' => $this->motherBlood,
            'mother_iris' => $this->motherIris,
            'mother_hair' => $this->motherHair,
            'mother_ear' => $this->motherEar,
            'mother_thalassemia' => $this->motherThalassemia,
        ];
    }

    /**
     * Variabel keluaran (kelas target) baris ini sebagai map kunci=>nilai.
     *
     * @return array<string,string>
     */
    public function outputClasses(): array
    {
        return [
            'baby_blood' => $this->babyBlood,
            'baby_iris' => $this->babyIris,
            'baby_hair' => $this->babyHair,
            'baby_ear' => $this->babyEar,
            'baby_thalassemia_risk' => $this->babyThalassemiaRisk,
        ];
    }
}
