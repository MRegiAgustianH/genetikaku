<?php

use App\Http\Requests\Public\PredictionRequest;
use App\Models\Phenotype;
use App\Models\ScreeningResult;
use Database\Factories\PhenotypeFactory;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Support\Arr;
use Tests\RefreshDatabaseWithoutSeeding;

uses(TestTrait::class, RefreshDatabaseWithoutSeeding::class);

/*
 * Property 15 menguji bahwa proses prediksi (Tahap 2) MENOLAK pengiriman
 * formulir Fenotipe bila satu atau lebih kategori belum dipilih, dan
 * menghasilkan kesalahan validasi yang menyebutkan kategori (field) yang belum
 * lengkap (Req 2.5).
 *
 * Pendekatan (feature-level, melalui seam validasi
 * App\Http\Requests\Public\PredictionRequest via route publik 'prediksi.store'
 * yang dijaga middleware 'screening.completed'):
 *   - Seed Data_Fenotipe terkontrol sehingga setiap kategori punya nilai valid.
 *   - Buat ScreeningResult dan letakkan id-nya di sesi ('screening_result_id')
 *     agar guard alur lolos.
 *   - Bangun payload VALID & LENGKAP untuk kedelapan field wajib
 *     (father_blood, father_iris, father_hair, father_ear, mother_blood,
 *     mother_iris, mother_hair, mother_ear).
 *   - Tiap iterasi: UNSET subset NON-KOSONG acak dari kedelapan field wajib
 *     sehingga pengiriman dijamin tidak lengkap; POST ke route('prediksi.store').
 *   - Pastikan ada kesalahan validasi yang menyebutkan SETIAP field yang
 *     dihapus, DAN tidak ada Hasil_Prediksi yang tersimpan.
 *   - Vektor boolean "drop mask" memvariasikan field yang dihapus lintas
 *     iterasi sehingga seluruh kedelapan field wajib tercakup.
 *
 * Prefiks unik PROP15_ dipakai untuk simbol lingkup-berkas guna menghindari
 * tabrakan simbol global dengan berkas tes lain.
 */

/**
 * Kedelapan field Fenotipe WAJIB pada formulir prediksi sebagai kunci yang
 * sekaligus merupakan kunci kesalahan validasi Laravel.
 *
 * @return list<string>
 */
function prop15RequiredFields(): array
{
    $fields = [];

    foreach (array_keys(PredictionRequest::CATEGORY_FIELDS) as $suffix) {
        foreach (PredictionRequest::PARENTS as $parent) {
            $fields[] = "{$parent}_{$suffix}";
        }
    }

    return $fields;
}

/**
 * Seed Data_Fenotipe terkontrol: untuk tiap kategori kanonik, buat seluruh
 * nilai yang valid sehingga PredictionRequest memiliki daftar nilai terdaftar.
 */
function prop15SeedPhenotypes(): void
{
    foreach (PhenotypeFactory::values() as $category => $values) {
        foreach ($values as $value) {
            Phenotype::query()->create([
                'category' => $category,
                'value' => $value,
            ]);
        }
    }
}

/**
 * Bangun payload prediksi yang LENGKAP & VALID: kedelapan field diisi nilai
 * pertama yang terdaftar pada Data_Fenotipe untuk kategori terkait.
 *
 * @return array<string, string>
 */
function prop15ValidPayload(): array
{
    $payload = [];

    foreach (PredictionRequest::CATEGORY_FIELDS as $suffix => $category) {
        $value = Phenotype::query()
            ->where('category', $category->value)
            ->value('value');

        foreach (PredictionRequest::PARENTS as $parent) {
            $payload["{$parent}_{$suffix}"] = $value;
        }
    }

    return $payload;
}

// Feature: genetikaku-expert-system, Property 15: Prediksi menolak kategori fenotipe yang belum dipilih
it('menolak pengiriman prediksi dengan kategori fenotipe yang belum dipilih dan menyebutkan field tersebut', function () {
    $this->withoutVite();

    prop15SeedPhenotypes();

    $screening = ScreeningResult::factory()->create();

    $fields = prop15RequiredFields();

    $this->forAll(
        // Satu boolean per field wajib menentukan apakah field itu dihapus.
        Generator\vector(count($fields), Generator\bool()),
    )->then(function (array $dropMask) use ($fields, $screening) {
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
        $payload = prop15ValidPayload();
        foreach ($dropped as $field) {
            Arr::forget($payload, $field);
        }

        $response = $this
            ->withSession(['screening_result_id' => $screening->id])
            ->post(route('prediksi.store'), $payload);

        // Setiap kategori/field yang belum dipilih harus disebut dalam
        // kesalahan validasi, dan tidak ada Hasil_Prediksi yang tersimpan.
        $response->assertSessionHasErrors($dropped);
        $this->assertDatabaseCount('prediction_results', 0);
    });
});
