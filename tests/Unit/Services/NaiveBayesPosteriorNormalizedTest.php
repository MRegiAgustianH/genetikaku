<?php

use App\Domain\PredictionOutcome;
use App\Domain\TrainingRow;
use App\Services\NaiveBayesClassifier;
use Eris\Generator;
use Eris\TestTrait;

uses(TestTrait::class);

/*
 * Property 7 menguji invarian tampilan: probabilitas posterior yang disertakan
 * pada PredictionOutcome ternormalisasi. Generator Data_Latih (nbTrainingRowGenerator)
 * dan daftar variabel keluaran (NB_OUTPUT_VARIABLES) dibagikan dari
 * NaiveBayesClassifierTest.php agar konsisten dengan property NB lainnya.
 */

// Feature: genetikaku-expert-system, Property 7: Probabilitas posterior yang ditampilkan ternormalisasi
it('menyertakan probabilitas posterior ternormalisasi untuk setiap variabel keluaran', function () {
    $classifier = new NaiveBayesClassifier();

    $this->forAll(
        // Baris dasar menjamin Data_Latih tidak kosong; atribut masukannya pasti
        // terdaftar pada Data_Latih sehingga lolos validasi input (Req 3.1).
        nbTrainingRowGenerator(),
        // Baris tambahan menambah variasi distribusi kelas/atribut (boleh 0..n).
        Generator\seq(nbTrainingRowGenerator()),
    )->then(function (TrainingRow $base, array $extra) use ($classifier) {
        /** @var list<TrainingRow> $training */
        $training = array_merge([$base], $extra);

        // Atribut masukan valid diambil dari baris dasar (nilainya pasti muncul
        // pada Data_Latih untuk atribut bersangkutan).
        $input = $base->inputAttributes();

        $outcome = $classifier->predict($input, $training);

        expect($outcome)->toBeInstanceOf(PredictionOutcome::class);

        // Kelima variabel keluaran wajib hadir pada probabilitas yang ditampilkan.
        expect(array_keys($outcome->probabilities))
            ->toEqualCanonicalizing(NB_OUTPUT_VARIABLES);

        foreach (NB_OUTPUT_VARIABLES as $variable) {
            $distribution = $outcome->probabilities[$variable];

            // Setiap probabilitas berada dalam rentang [0, 1].
            foreach ($distribution as $class => $probability) {
                expect($probability)->toBeGreaterThanOrEqual(0.0, "P({$variable}/{$class}) >= 0");
                expect($probability)->toBeLessThanOrEqual(1.0, "P({$variable}/{$class}) <= 1");
            }

            // Jumlah probabilitas pada satu variabel = 1 (toleransi floating point).
            expect(array_sum($distribution))->toEqualWithDelta(1.0, 1e-9);
        }
    });
});
