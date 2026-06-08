<?php

use App\Domain\PredictionOutcome;
use App\Services\Exceptions\EmptyTrainingDataException;
use App\Services\NaiveBayesClassifier;
use Eris\Generator;
use Eris\TestTrait;

uses(TestTrait::class);

/*
 * Pool nilai bervariasi (sengaja diberi prefix unik NB_P10_* agar tidak
 * bertabrakan dengan konstanta/fungsi global di file property test NB lain).
 * Isi nilai atribut tidak relevan untuk Property 10: guard Data_Latih kosong
 * dievaluasi paling awal di predict(), sebelum nilai input apa pun diperiksa.
 */
const NB_P10_VALUES = ['A', 'B', 'AB', 'O', 'Cokelat', 'Lurus', 'Normal', 'Carrier', '', 'X9_!'];

const NB_P10_INPUT_KEYS = [
    'father_blood',
    'father_iris',
    'father_hair',
    'father_ear',
    'father_thalassemia',
    'mother_blood',
    'mother_iris',
    'mother_hair',
    'mother_ear',
    'mother_thalassemia',
];

// Feature: genetikaku-expert-system, Property 10: Data latih kosong membatalkan perhitungan
it('membatalkan perhitungan dan melempar EmptyTrainingDataException ketika Data_Latih kosong', function () {
    $classifier = new NaiveBayesClassifier();

    $this->forAll(
        // Peta atribut masukan dengan nilai acak: konten tak relevan karena
        // Data_Latih kosong men-short-circuit perhitungan lebih dulu (Req 3.8).
        Generator\map(
            static function (array $values): array {
                $input = [];
                foreach (NB_P10_INPUT_KEYS as $index => $key) {
                    $input[$key] = $values[$index];
                }

                return $input;
            },
            Generator\tuple(
                ...array_map(
                    static fn (): Eris\Generator => Generator\elements(...NB_P10_VALUES),
                    NB_P10_INPUT_KEYS
                )
            )
        )
    )->then(function (array $input) use ($classifier) {
        $outcome = null;

        try {
            // Data_Latih kosong: harus dibatalkan, bukan mengembalikan prediksi.
            $outcome = $classifier->predict($input, []);
            $threw = false;
        } catch (EmptyTrainingDataException $e) {
            $threw = true;
        }

        // Perhitungan dibatalkan via exception, bukan mengembalikan PredictionOutcome.
        expect($threw)->toBeTrue('predict() harus melempar EmptyTrainingDataException saat Data_Latih kosong');
        expect($outcome)->toBeNull();
        expect($outcome)->not->toBeInstanceOf(PredictionOutcome::class);
    });
});
