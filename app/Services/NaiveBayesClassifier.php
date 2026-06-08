<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\PhenotypeCategory;
use App\Domain\PredictionOutcome;
use App\Domain\ScreeningCategory;
use App\Domain\ThalassemiaRisk;
use App\Domain\TrainingRow;
use App\Services\Exceptions\EmptyTrainingDataException;
use App\Services\Exceptions\InvalidAttributeException;

/**
 * Mesin_Naive_Bayes (Tahap 3).
 *
 * Kelas murni (tanpa I/O DB/HTTP): menerima atribut masukan dan Data_Latih
 * sebagai argumen eksplisit lalu mengembalikan hasil deterministik. Hal ini
 * membuatnya mudah diuji terisolasi (property-based testing) sesuai keputusan
 * desain "mesin domain sebagai service murni".
 *
 * Tanggung jawab task 4.1 (file ini): validasi input & guard Data_Latih
 * - {@see EmptyTrainingDataException} bila Data_Latih kosong (Req 3.8).
 * - {@see InvalidAttributeException} bila ada nilai atribut masukan yang tidak
 *   terdaftar pada Data_Latih maupun kategori Hasil_Skrining_Orang_Tua yang
 *   valid (Req 3.1).
 *
 * Perhitungan prior/likelihood (Laplace)/posterior dan pemilihan kelas
 * diimplementasikan di {@see self::compute()} (task 4.2).
 *
 * Requirements: 3.1, 3.8 (task 4.1); 3.3, 3.4, 3.5, 3.6, 3.7, 4.1, 4.2, 4.3 (task 4.2)
 */
final class NaiveBayesClassifier
{
    /**
     * Atribut masukan yang menyimpan Hasil_Skrining_Orang_Tua. Untuk atribut
     * ini, selain nilai yang muncul pada Data_Latih, nilai kategori
     * {@see ScreeningCategory} yang valid juga diterima (Req 3.1).
     *
     * @var list<string>
     */
    private const THALASSEMIA_ATTRIBUTES = [
        'father_thalassemia',
        'mother_thalassemia',
    ];

    /**
     * Klasifikasikan karakteristik fisik bayi dan Risiko_Thalassemia_Bayi.
     *
     * @param  array<string,string>  $input  atribut masukan (fenotipe ayah/ibu + Hasil_Skrining_Orang_Tua)
     * @param  list<TrainingRow>  $training  Data_Latih
     *
     * @throws EmptyTrainingDataException  bila $training kosong (Req 3.8)
     * @throws InvalidAttributeException  bila ada nilai input tak terdaftar (Req 3.1)
     */
    public function predict(array $input, array $training): PredictionOutcome
    {
        $this->guardAgainstEmptyTrainingData($training);
        $this->guardAgainstUnknownAttributeValues($input, $training);

        return $this->compute($input, $training);
    }

    /**
     * Req 3.8: batalkan perhitungan bila Data_Latih kosong.
     *
     * @param  list<TrainingRow>  $training
     *
     * @throws EmptyTrainingDataException
     */
    private function guardAgainstEmptyTrainingData(array $training): void
    {
        if ($training === []) {
            throw EmptyTrainingDataException::create();
        }
    }

    /**
     * Req 3.1: setiap nilai atribut masukan harus terdaftar pada Data_Latih
     * untuk atribut tersebut; khusus atribut Hasil_Skrining_Orang_Tua, nilai
     * kategori {@see ScreeningCategory} yang valid juga diterima.
     *
     * @param  array<string,string>  $input
     * @param  list<TrainingRow>  $training
     *
     * @throws InvalidAttributeException
     */
    private function guardAgainstUnknownAttributeValues(array $input, array $training): void
    {
        $allowedValues = $this->allowedValuesByAttribute($training);

        foreach ($input as $attribute => $value) {
            $allowedForAttribute = $allowedValues[$attribute] ?? [];

            if (in_array($attribute, self::THALASSEMIA_ATTRIBUTES, true)) {
                foreach (ScreeningCategory::cases() as $category) {
                    $allowedForAttribute[$category->value] = true;
                }
            }

            if (! isset($allowedForAttribute[$value])) {
                throw InvalidAttributeException::forValue((string) $attribute, $value);
            }
        }
    }

    /**
     * Kumpulkan himpunan nilai distinct yang muncul pada Data_Latih untuk
     * setiap atribut masukan. Bentuknya map attribute => (map value => true)
     * agar pemeriksaan keanggotaan O(1).
     *
     * @param  list<TrainingRow>  $training
     * @return array<string,array<string,true>>
     */
    private function allowedValuesByAttribute(array $training): array
    {
        $allowed = [];

        foreach ($training as $row) {
            foreach ($row->inputAttributes() as $attribute => $value) {
                $allowed[$attribute][$value] = true;
            }
        }

        return $allowed;
    }

    /**
     * Perhitungan Naive Bayes (task 4.2).
     *
     * Tahap yang harus diimplementasikan di sini (lihat design.md, bagian
     * "NaiveBayesClassifier"):
     *  1. Prior      : P(c) = count(class = c) / N untuk tiap variabel keluaran (Req 3.3).
     *  2. Likelihood : P(x_i|c) = (count(x_i,c) + 1) / (count(c) + V_i) dengan
     *                  Laplace smoothing, V_i = jumlah nilai distinct atribut i (Req 3.4, 3.7).
     *  3. Posterior  : score(c) = P(c) * Π_i P(x_i|c) (Req 3.5).
     *  4. Pemilihan  : kelas dengan score(c) terbesar per variabel keluaran (Req 3.6).
     *  5. Normalisasi: probabilitas posterior score(c)/Σ score untuk ditampilkan (Req 4.3).
     *
     * Variabel keluaran berasal dari {@see TrainingRow::outputClasses()}:
     * baby_blood, baby_iris, baby_hair, baby_ear (4 kategori fisik, Req 4.1)
     * dan baby_thalassemia_risk (Risiko_Thalassemia_Bayi, Req 4.2). Hasil
     * akhir dirakit menjadi {@see PredictionOutcome}.
     *
     * Pada saat ini guard input/Data_Latih (task 4.1) sudah dijamin lulus.
     *
     * @param  array<string,string>  $input
     * @param  list<TrainingRow>  $training
     */
    private function compute(array $input, array $training): PredictionOutcome
    {
        $n = count($training);

        // V_i: jumlah nilai distinct setiap atribut masukan pada Data_Latih.
        // Dihitung sekali dan dipakai konsisten untuk seluruh variabel keluaran
        // sehingga likelihood (dan posterior, Property 5) deterministik.
        $distinctValueCounts = $this->distinctValueCounts($training);

        // Variabel keluaran mengikuti urutan TrainingRow::outputClasses():
        // baby_blood, baby_iris, baby_hair, baby_ear, baby_thalassemia_risk.
        $outputVariables = array_keys($training[0]->outputClasses());

        /** @var array<string,array<string,float>> $probabilities */
        $probabilities = [];
        /** @var array<string,string> $selected */
        $selected = [];

        foreach ($outputVariables as $variable) {
            // Skor posterior tak ternormalisasi per kelas: P(c) * Π_i P(x_i|c).
            $scores = $this->unnormalizedScores($variable, $input, $training, $n, $distinctValueCounts);

            // Req 3.6: kelas dengan skor posterior terbesar dipilih.
            $selected[$variable] = $this->classWithMaxScore($scores);

            // Req 4.3: posterior ternormalisasi untuk ditampilkan.
            $probabilities[$variable] = $this->normalize($scores);
        }

        // Req 4.1: empat kategori fisik dipetakan ke nilai PhenotypeCategory.
        $physical = [
            PhenotypeCategory::GolonganDarah->value => $selected['baby_blood'],
            PhenotypeCategory::WarnaIris->value => $selected['baby_iris'],
            PhenotypeCategory::TeksturRambut->value => $selected['baby_hair'],
            PhenotypeCategory::BentukCuping->value => $selected['baby_ear'],
        ];

        // Req 4.2: Risiko_Thalassemia_Bayi sebagai enum dari kelas terpilih.
        $thalassemiaRisk = ThalassemiaRisk::from($selected['baby_thalassemia_risk']);

        return new PredictionOutcome($physical, $thalassemiaRisk, $probabilities);
    }

    /**
     * Hitung skor posterior tak ternormalisasi setiap kelas pada satu variabel
     * keluaran: `score(c) = P(c) * Π_i P(x_i | c)` (Req 3.3, 3.4, 3.5, 3.7).
     *
     * @param  array<string,string>  $input
     * @param  list<TrainingRow>  $training
     * @param  array<string,int>  $distinctValueCounts  V_i per atribut masukan
     * @return array<string,float>  map kelas => skor tak ternormalisasi (selalu > 0)
     */
    private function unnormalizedScores(
        string $variable,
        array $input,
        array $training,
        int $n,
        array $distinctValueCounts,
    ): array {
        // count(class = c) untuk variabel ini, dalam urutan kemunculan pertama.
        $classCounts = [];
        foreach ($training as $row) {
            $class = $row->outputClasses()[$variable];
            $classCounts[$class] = ($classCounts[$class] ?? 0) + 1;
        }

        $scores = [];

        foreach ($classCounts as $class => $countOfClass) {
            $class = (string) $class;

            // Req 3.3: prior P(c) = count(c) / N.
            $score = $countOfClass / $n;

            // Req 3.4 & 3.7: likelihood dengan Laplace smoothing,
            // P(x_i|c) = (count(x_i,c) + 1) / (count(c) + V_i). Smoothing
            // menjamin likelihood > 0 walau kombinasi (x_i, c) tak muncul.
            foreach ($input as $attribute => $value) {
                $vi = $distinctValueCounts[$attribute] ?? 0;
                $jointCount = $this->jointCount($training, $variable, (string) $class, (string) $attribute, $value);

                $score *= ($jointCount + 1) / ($countOfClass + $vi);
            }

            $scores[$class] = $score;
        }

        return $scores;
    }

    /**
     * count(x_i = $value, class = $class) pada Data_Latih: jumlah baris yang
     * nilai atribut masukan $attribute-nya $value sekaligus variabel keluaran
     * $variable-nya $class.
     *
     * @param  list<TrainingRow>  $training
     */
    private function jointCount(
        array $training,
        string $variable,
        string $class,
        string $attribute,
        string $value,
    ): int {
        $count = 0;

        foreach ($training as $row) {
            if ((string) $row->outputClasses()[$variable] !== $class) {
                continue;
            }

            if (($row->inputAttributes()[$attribute] ?? null) === $value) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Pilih kelas dengan skor terbesar (Req 3.6). Bila terjadi seri, kelas yang
     * muncul lebih dulu dipertahankan; tetap memenuhi syarat skor terpilih >=
     * skor kelas lain.
     *
     * @param  array<string,float>  $scores
     */
    private function classWithMaxScore(array $scores): string
    {
        $bestClass = null;
        $bestScore = -INF;

        foreach ($scores as $class => $score) {
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestClass = (string) $class;
            }
        }

        return (string) $bestClass;
    }

    /**
     * Normalisasi skor posterior menjadi distribusi yang berjumlah 1 (Req 4.3).
     * Karena setiap skor > 0 (prior kelas yang muncul > 0 dan likelihood > 0),
     * jumlahnya selalu > 0.
     *
     * @param  array<string,float>  $scores
     * @return array<string,float>
     */
    private function normalize(array $scores): array
    {
        $sum = array_sum($scores);

        $normalized = [];
        foreach ($scores as $class => $score) {
            $normalized[(string) $class] = $sum > 0.0 ? $score / $sum : 0.0;
        }

        return $normalized;
    }

    /**
     * V_i: jumlah nilai distinct setiap atribut masukan pada Data_Latih.
     *
     * @param  list<TrainingRow>  $training
     * @return array<string,int>  map atribut masukan => jumlah nilai distinct
     */
    private function distinctValueCounts(array $training): array
    {
        $distinct = [];

        foreach ($training as $row) {
            foreach ($row->inputAttributes() as $attribute => $value) {
                $distinct[$attribute][$value] = true;
            }
        }

        return array_map('count', $distinct);
    }
}
