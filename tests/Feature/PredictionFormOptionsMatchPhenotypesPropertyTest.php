<?php

use App\Domain\PhenotypeCategory;
use App\Http\Middleware\EnsureScreeningCompleted;
use App\Models\Phenotype;
use App\Models\ScreeningResult;
use Eris\Generator;
use Eris\TestTrait;
use Inertia\Testing\AssertableInertia;
use Tests\RefreshDatabaseWithoutSeeding;

uses(TestTrait::class, RefreshDatabaseWithoutSeeding::class);

/*
 * Property 14 menguji konsistensi opsi nilai pada form prediksi Tahap 2 di
 * level rute/HTTP + Inertia (route 'prediksi.create', prop 'phenotypeOptions').
 *
 * Untuk sembarang himpunan Data_Fenotipe (nilai per kategori), opsi nilai yang
 * tersedia untuk setiap kategori pada form prediksi HARUS sama persis dengan
 * himpunan nilai yang terdaftar untuk kategori tersebut pada tabel `phenotypes`
 * saat itu (Req 2.2, 13.2). Selain itu, keempat kunci kategori selalu hadir
 * (daftar kosong bila tidak ada nilai terdaftar).
 *
 * Pendekatan (Pest + Eris, feature-level):
 *   - Tiap iterasi: bangkitkan jumlah nilai acak (0-5) per kategori dengan
 *     nilai unik dalam satu kategori, lalu persist baris Phenotype.
 *   - Buat ScreeningResult dan taruh id-nya di sesi agar guard
 *     'screening.completed' (EnsureScreeningCompleted) lolos.
 *   - GET route('prediksi.create'); pastikan komponen 'public/prediction/form'
 *     dan baca prop 'phenotypeOptions'. Untuk tiap kategori, himpunan opsi
 *     HARUS sama persis (dibandingkan sebagai himpunan terurut) dengan nilai
 *     yang terdaftar; keempat kunci kategori HARUS hadir.
 *   - Reset tabel `phenotypes` antar-iterasi untuk himpunan terkontrol.
 *
 * Prefiks unik PROP14_ dipakai untuk simbol lingkup-berkas guna menghindari
 * tabrakan simbol global dengan berkas tes lain.
 */

/**
 * Generator jumlah nilai per kategori (0-5 masing-masing dari 4 kategori) plus
 * sebuah salt numerik untuk memvariasikan string nilai antar-iterasi. Ukuran
 * dibatasi kecil agar pembuatan baris DB pada >=100 iterasi tetap berkinerja.
 */
function prop14PhenotypeSetGenerator(): \Eris\Generator
{
    return Generator\tuple(
        Generator\choose(0, 5),
        Generator\choose(0, 5),
        Generator\choose(0, 5),
        Generator\choose(0, 5),
        Generator\choose(0, 1000000),
    );
}

// Feature: genetikaku-expert-system, Property 14: Opsi form prediksi sama dengan Data_Fenotipe terkini
it('opsi form prediksi sama persis dengan Data_Fenotipe terkini', function () {
    // Nonaktifkan Vite agar render Inertia tidak bergantung pada aset terbuild.
    $this->withoutVite();

    $this->forAll(
        prop14PhenotypeSetGenerator(),
    )->then(function (array $spec) {
        $salt = $spec[4];
        $categories = PhenotypeCategory::cases();

        // Mulai dari state bersih tiap iterasi agar himpunan terkontrol.
        Phenotype::query()->delete();
        ScreeningResult::query()->delete();

        // Bangun Data_Fenotipe acak: nilai unik dalam tiap kategori.
        $expected = [];

        foreach ($categories as $index => $category) {
            $count = $spec[$index];
            $values = [];

            for ($i = 0; $i < $count; $i++) {
                // Unik dalam kategori karena memuat indeks $i; bervariasi
                // antar-iterasi karena memuat $salt.
                $value = sprintf('PROP14-c%d-v%d-s%d', $index, $i, $salt);
                $values[] = $value;

                Phenotype::create([
                    'category' => $category,
                    'value' => $value,
                ]);
            }

            sort($values);
            $expected[$category->value] = $values;
        }

        // Lolos guard 'screening.completed': butuh screening_result_id valid di sesi.
        $screening = ScreeningResult::factory()->create();
        $this->withSession([EnsureScreeningCompleted::SESSION_KEY => $screening->id]);

        $response = $this->get(route('prediksi.create'));
        $response->assertOk();

        $response->assertInertia(function (AssertableInertia $page) use ($expected, $categories) {
            // Halaman React 'public/prediction/form' dibangun pada task 7.5;
            // properti ini memvalidasi kontrak prop, bukan keberadaan view.
            $page->component('public/prediction/form', false);

            $options = $page->toArray()['props']['phenotypeOptions'] ?? null;
            expect($options)->toBeArray();

            // Keempat kunci kategori selalu hadir.
            expect(count($options))->toBe(count($categories));

            foreach ($categories as $category) {
                expect(array_key_exists($category->value, $options))->toBeTrue();

                $actual = $options[$category->value];
                sort($actual);

                // Himpunan opsi sama persis dengan nilai terdaftar untuk kategori.
                expect($actual)->toBe($expected[$category->value]);
            }
        });
    });
});
