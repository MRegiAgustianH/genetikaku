<?php

namespace App\Http\Requests\Admin;

use App\Domain\PhenotypeCategory;
use App\Domain\ScreeningCategory;
use App\Domain\ThalassemiaRisk;
use App\Models\Phenotype;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi penyimpanan/pembaruan baris Data_Latih (Req 14.3).
 *
 * Aturan inti: setiap atribut fenotipe (golongan darah, iris, rambut, cuping
 * untuk ayah/ibu/bayi) harus bernilai yang TERDAFTAR pada Data_Fenotipe untuk
 * PhenotypeCategory yang sesuai. Status Thalassemia orang tua harus berupa
 * nilai ScreeningCategory yang valid, dan Risiko_Thalassemia_Bayi harus berupa
 * nilai ThalassemiaRisk yang valid.
 *
 * Karena himpunan nilai fenotipe bersifat dinamis (dikelola Admin), daftar
 * nilai yang diizinkan dibangun dari tabel `phenotypes` saat validasi
 * berlangsung, lalu dipakai melalui Rule::in(...).
 *
 * Seam validasi ini sengaja dipisah agar property test (Properti 13 / task
 * 11.8) dapat menguji penolakan nilai di luar Data_Fenotipe maupun kategori
 * skrining secara langsung.
 */
class TrainingDataRequest extends FormRequest
{
    /**
     * Pemetaan kolom fenotipe -> PhenotypeCategory yang berlaku.
     *
     * @var array<string, PhenotypeCategory>
     */
    private const PHENOTYPE_COLUMNS = [
        'father_blood' => PhenotypeCategory::GolonganDarah,
        'father_iris' => PhenotypeCategory::WarnaIris,
        'father_hair' => PhenotypeCategory::TeksturRambut,
        'father_ear' => PhenotypeCategory::BentukCuping,
        'mother_blood' => PhenotypeCategory::GolonganDarah,
        'mother_iris' => PhenotypeCategory::WarnaIris,
        'mother_hair' => PhenotypeCategory::TeksturRambut,
        'mother_ear' => PhenotypeCategory::BentukCuping,
        'baby_blood' => PhenotypeCategory::GolonganDarah,
        'baby_iris' => PhenotypeCategory::WarnaIris,
        'baby_hair' => PhenotypeCategory::TeksturRambut,
        'baby_ear' => PhenotypeCategory::BentukCuping,
    ];

    /**
     * Otorisasi ditangani oleh middleware `admin` pada grup route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang berlaku untuk request.
     *
     * Daftar nilai fenotipe yang diizinkan dibangun dari Data_Fenotipe terkini
     * (satu query, dikelompokkan per kategori) agar penolakan nilai di luar
     * registry konsisten dengan Mesin_Naive_Bayes.
     *
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        $allowedByCategory = $this->allowedPhenotypeValues();

        $screeningValues = array_map(
            static fn (ScreeningCategory $c): string => $c->value,
            ScreeningCategory::cases(),
        );

        $riskValues = array_map(
            static fn (ThalassemiaRisk $r): string => $r->value,
            ThalassemiaRisk::cases(),
        );

        $rules = [];

        foreach (self::PHENOTYPE_COLUMNS as $column => $category) {
            $allowed = $allowedByCategory[$category->value] ?? [];

            $rules[$column] = ['required', 'string', Rule::in($allowed)];
        }

        $rules['father_thalassemia'] = ['required', 'string', Rule::in($screeningValues)];
        $rules['mother_thalassemia'] = ['required', 'string', Rule::in($screeningValues)];
        $rules['baby_thalassemia_risk'] = ['required', 'string', Rule::in($riskValues)];

        return $rules;
    }

    /**
     * Pesan kesalahan kustom: nilai di luar registry ditolak dengan jelas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach (array_keys(self::PHENOTYPE_COLUMNS) as $column) {
            $messages["{$column}.in"] = 'Nilai :attribute tidak terdaftar pada Data_Fenotipe.';
        }

        $messages['father_thalassemia.in'] = 'Status Thalassemia ayah bukan kategori Hasil_Skrining yang valid.';
        $messages['mother_thalassemia.in'] = 'Status Thalassemia ibu bukan kategori Hasil_Skrining yang valid.';
        $messages['baby_thalassemia_risk.in'] = 'Risiko Thalassemia bayi bukan klasifikasi risiko yang valid.';

        return $messages;
    }

    /**
     * Nama atribut yang lebih manusiawi untuk pesan kesalahan.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'father_blood' => 'Golongan Darah Ayah',
            'father_iris' => 'Warna Iris Mata Ayah',
            'father_hair' => 'Tekstur Rambut Ayah',
            'father_ear' => 'Bentuk Cuping Telinga Ayah',
            'father_thalassemia' => 'Status Thalassemia Ayah',
            'mother_blood' => 'Golongan Darah Ibu',
            'mother_iris' => 'Warna Iris Mata Ibu',
            'mother_hair' => 'Tekstur Rambut Ibu',
            'mother_ear' => 'Bentuk Cuping Telinga Ibu',
            'mother_thalassemia' => 'Status Thalassemia Ibu',
            'baby_blood' => 'Golongan Darah Bayi',
            'baby_iris' => 'Warna Iris Mata Bayi',
            'baby_hair' => 'Tekstur Rambut Bayi',
            'baby_ear' => 'Bentuk Cuping Telinga Bayi',
            'baby_thalassemia_risk' => 'Risiko Thalassemia Bayi',
        ];
    }

    /**
     * Bangun daftar nilai fenotipe valid per kategori dari Data_Fenotipe terkini.
     *
     * @return array<string, list<string>>
     */
    private function allowedPhenotypeValues(): array
    {
        $allowed = [];

        foreach (Phenotype::query()->get(['category', 'value']) as $phenotype) {
            $category = $phenotype->category instanceof PhenotypeCategory
                ? $phenotype->category->value
                : (string) $phenotype->category;

            $allowed[$category][] = $phenotype->value;
        }

        return $allowed;
    }
}
