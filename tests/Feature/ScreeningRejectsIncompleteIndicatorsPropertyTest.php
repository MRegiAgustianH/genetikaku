<?php

use App\Http\Requests\Public\ScreeningRequest;
use App\Models\ScreeningResult;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Support\Arr;
use Tests\RefreshDatabaseWithoutSeeding;

uses(TestTrait::class, RefreshDatabaseWithoutSeeding::class);

/*
 * Property 2 menguji bahwa proses skrining (Tahap 1) MENOLAK pengiriman bila
 * satu atau lebih Indikator_Skrining wajib belum lengkap, dan menghasilkan
 * kesalahan validasi yang menyebutkan indikator (field) yang belum lengkap
 * (Req 1.4).
 *
 * Pendekatan (feature-level, melalui seam validasi
 * App\Http\Requests\Public\ScreeningRequest via route publik 'skrining.store'):
 *   - Bangun payload skrining yang sepenuhnya VALID & LENGKAP (nama ayah/ibu +
 *     12 indikator boolean: 6 untuk ayah, 6 untuk ibu).
 *   - Tiap iterasi: hapus (unset) subset NON-KOSONG acak dari field wajib
 *     sehingga pengiriman dijamin tidak lengkap. Catatan: 'required'+'boolean'
 *     menganggap `false` tetap "terisi", maka untuk membuat sebuah field
 *     "hilang" kita meng-UNSET-nya dari payload, bukan menyetelnya `false`.
 *   - POST ke route('skrining.store'); pastikan ada kesalahan validasi yang
 *     menyebutkan SETIAP field yang dihapus (kunci ber-titik seperti
 *     'father.riwayat_keluarga'), DAN tidak ada baris screening_results tersimpan.
 *   - Vektor boolean "drop mask" memvariasikan field yang dihapus lintas
 *     iterasi sehingga seluruh field wajib tercakup.
 *
 * Prefiks unik PROP2_ dipakai untuk simbol lingkup-berkas guna menghindari
 * tabrakan simbol global dengan berkas tes lain.
 */

/**
 * Seluruh field WAJIB pada formulir skrining sebagai kunci ber-titik yang
 * sekaligus merupakan kunci kesalahan validasi Laravel.
 *
 * @return list<string>
 */
function prop2RequiredFields(): array
{
    $fields = ['father_name', 'mother_name'];

    foreach (array_keys(ScreeningRequest::INDICATORS) as $key) {
        $fields[] = "father.{$key}";
        $fields[] = "mother.{$key}";
    }

    return $fields;
}

/**
 * Bangun payload skrining yang LENGKAP & VALID: nama ayah/ibu terisi dan
 * keenam indikator boolean hadir untuk masing-masing orang tua.
 *
 * @return array<string, mixed>
 */
function prop2ValidPayload(): array
{
    $payload = [
        'father_name' => 'Budi Santoso',
        'mother_name' => 'Siti Aminah',
    ];

    foreach (array_keys(ScreeningRequest::INDICATORS) as $key) {
        $payload['father'][$key] = true;
        $payload['mother'][$key] = true;
    }

    return $payload;
}

// Feature: genetikaku-expert-system, Property 2: Skrining menolak indikator yang tidak lengkap
it('menolak pengiriman skrining dengan indikator wajib yang belum lengkap dan menyebutkan field tersebut', function () {
    $fields = prop2RequiredFields();

    $this->forAll(
        // Satu boolean per field wajib menentukan apakah field itu dihapus.
        Generator\vector(count($fields), Generator\bool()),
    )->then(function (array $dropMask) use ($fields) {
        // Tentukan field yang akan dihapus; pastikan subset NON-KOSONG agar
        // pengiriman benar-benar tidak lengkap.
        $dropped = [];
        foreach ($fields as $index => $field) {
            if ($dropMask[$index] ?? false) {
                $dropped[] = $field;
            }
        }

        if ($dropped === []) {
            $dropped[] = $fields[0];
        }

        // Mulai dari payload lengkap-valid lalu unset setiap field yang dipilih.
        $payload = prop2ValidPayload();
        foreach ($dropped as $field) {
            Arr::forget($payload, $field);
        }

        $before = ScreeningResult::query()->count();

        $response = $this->post(route('skrining.store'), $payload);

        // Setiap indikator/field yang hilang harus disebut dalam kesalahan
        // validasi, dan tidak ada Hasil_Skrining yang tersimpan.
        $response->assertSessionHasErrors($dropped);
        expect(ScreeningResult::query()->count())->toBe($before);
    });
});
