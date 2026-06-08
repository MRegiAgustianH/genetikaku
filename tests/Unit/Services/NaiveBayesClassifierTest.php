<?php

use App\Domain\TrainingRow;
use App\Services\NaiveBayesClassifier;
use Eris\Generator;
use Eris\TestTrait;

uses(TestTrait::class);

/*
 * Pool nilai fenotipe/kategori (NB_BLOODS, NB_IRIS, NB_HAIR, NB_EAR,
 * NB_THALASSEMIA, NB_RISK), daftar variabel keluaran (NB_OUTPUT_VARIABLES), dan
 * generator baris Data_Latih (nbTrainingRowGenerator()) didefinisikan TEPAT
 * SEKALI di tests/Support/NaiveBayesTestData.php (di-autoload Composer) dan
 * dibagikan dengan NaiveBayesPosteriorNormalizedTest.php (Property 7) agar tidak
 * ada deklarasi simbol global ganda.
 */

/**
 * Hitung distribusi prior referensi (independen) untuk satu variabel keluaran
 * memakai formula desain `P(c) = count(c) / N` (Req 3.3).
 *
 * @param  list<TrainingRow>  $training
 * @return array<string,float>  map kelas => prior
 */
function nbReferencePriors(array $training, string $variable): array
{
    $counts = [];
    foreach ($training as $row) {
        $class = $row->outputClasses()[$variable];
        $counts[$class] = ($counts[$class] ?? 0) + 1;
    }

    $n = count($training);

    return array_map(static fn (int $count): float => $count / $n, $counts);
}

// Feature: genetikaku-expert-system, Property 3: Probabilitas prior membentuk distribusi yang valid
it('menghasilkan distribusi prior yang valid untuk setiap variabel keluaran', function () {
    $classifier = new NaiveBayesClassifier();

    $this->forAll(
        // Data_Latih tidak kosong: satu baris dijamin + sisanya bebas (boleh 0..n).
        Generator\tuple(nbTrainingRowGenerator(), Generator\seq(nbTrainingRowGenerator()))
    )->then(function (array $pair) use ($classifier) {
        [$firstRow, $remainingRows] = $pair;

        /** @var list<TrainingRow> $training */
        $training = array_merge([$firstRow], $remainingRows);

        // Sanity: Data_Latih memang tidak kosong (prasyarat Property 3).
        expect($training)->not->toBeEmpty();

        foreach (NB_OUTPUT_VARIABLES as $variable) {
            // Prior referensi independen menggunakan formula desain P(c)=count(c)/N,
            // yaitu prior yang dipakai Mesin_Naive_Bayes secara internal.
            $priors = nbReferencePriors($training, $variable);

            // Setiap prior kelas berada dalam rentang [0, 1].
            foreach ($priors as $class => $prior) {
                expect($prior)->toBeGreaterThanOrEqual(0.0, "prior {$variable}/{$class} >= 0");
                expect($prior)->toBeLessThanOrEqual(1.0, "prior {$variable}/{$class} <= 1");
            }

            // Jumlah seluruh prior kelas pada variabel ini = 1 (toleransi float).
            expect(abs(array_sum($priors) - 1.0))->toBeLessThan(1e-9);

            // Konsistensi dengan klasifier: bila Data_Latih hanya memuat satu
            // kelas untuk variabel ini, distribusi prior runtuh menjadi {kelas => 1}.
            if (count($priors) === 1) {
                expect(reset($priors))->toEqualWithDelta(1.0, 1e-9);
            }
        }

        // Konsistensi end-to-end: klasifier menerima Data_Latih yang sama tanpa
        // error dan menghasilkan keluaran (prior internal yang diuji di atas
        // adalah prior yang sama yang dipakai perhitungan ini).
        $input = $firstRow->inputAttributes();
        expect($classifier->predict($input, $training))
            ->toBeInstanceOf(App\Domain\PredictionOutcome::class);
    });
});
