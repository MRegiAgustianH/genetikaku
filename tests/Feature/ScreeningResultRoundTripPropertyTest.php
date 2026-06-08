<?php

use App\Domain\ScreeningCategory;
use App\Models\ScreeningResult;
use Eris\Generator;
use Eris\TestTrait;
use Tests\RefreshDatabaseWithoutSeeding;

uses(TestTrait::class, RefreshDatabaseWithoutSeeding::class);

/*
 * Property 11 menguji invarian round-trip penyimpanan Hasil_Skrining (Req 1.5):
 * untuk Hasil_Skrining apa pun yang dihitung (nama ayah, nama ibu, hasil ayah,
 * hasil ibu), menyimpannya lalu memuatnya kembali dari penyimpanan HARUS
 * mengembalikan nilai keempat field yang identik.
 *
 * Pendekatan (Pest + Eris, lewat model Eloquent ScreeningResult):
 *   - Bangkitkan nama ayah/ibu acak (string nama non-kosong) dan hasil
 *     ayah/ibu acak dari ketiga kasus ScreeningCategory (Normal, Carrier,
 *     Berisiko Tinggi).
 *   - Simpan via ScreeningResult::create([...]) lalu muat ulang instans baru
 *     dari basis data (ScreeningResult::find($id)).
 *   - Pastikan keempat field identik: nama sama persis, dan father_result /
 *     mother_result kembali sebagai instans enum ScreeningCategory yang sama.
 *
 * Prefiks unik PROP11_ dipakai untuk simbol lingkup-berkas guna menghindari
 * tabrakan simbol global dengan berkas tes lain.
 */

/**
 * Generator tuple (nama ayah, nama ibu, hasil ayah, hasil ibu) untuk membentuk
 * satu Hasil_Skrining acak. Nama memakai generator nama (selalu non-kosong);
 * hasil dipilih dari ketiga kasus ScreeningCategory.
 */
function prop11ScreeningGenerator(): Generator
{
    return Generator\tuple(
        Generator\names(),
        Generator\names(),
        Generator\elements(...ScreeningCategory::cases()),
        Generator\elements(...ScreeningCategory::cases()),
    );
}

// Feature: genetikaku-expert-system, Property 11: Penyimpanan Hasil_Skrining round trip
it('menyimpan lalu memuat Hasil_Skrining mengembalikan keempat field identik', function () {
    // Eris default 100 iterasi (lihat Eris\TestTrait::$iterations).
    $this->forAll(
        prop11ScreeningGenerator(),
    )
        ->then(function (array $skrining) {
            [$fatherName, $motherName, $fatherResult, $motherResult] = $skrining;

            // Mulai dari state bersih tiap iterasi agar tabel terkontrol.
            ScreeningResult::query()->delete();

            // Simpan Hasil_Skrining yang dihitung.
            $saved = ScreeningResult::create([
                'father_name' => $fatherName,
                'mother_name' => $motherName,
                'father_result' => $fatherResult,
                'mother_result' => $motherResult,
            ]);

            // Muat ulang instans baru dari penyimpanan (bukan dari memori).
            $loaded = ScreeningResult::find($saved->id);

            // Keempat field harus identik dengan yang disimpan.
            expect($loaded->father_name)->toBe($fatherName);
            expect($loaded->mother_name)->toBe($motherName);
            expect($loaded->father_result)->toBe($fatherResult);
            expect($loaded->mother_result)->toBe($motherResult);
        });
});
