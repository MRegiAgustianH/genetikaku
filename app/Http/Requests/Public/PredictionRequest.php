<?php

namespace App\Http\Requests\Public;

use App\Domain\PhenotypeCategory;
use App\Models\Phenotype;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi formulir input Fenotipe Tahap 2 (Req 2.2, 2.5).
 *
 * Memvalidasi keempat kategori Fenotipe WAJIB untuk MASING-MASING orang tua
 * (ayah & ibu). Setiap nilai harus termasuk daftar nilai terdaftar pada
 * Data_Fenotipe (`phenotypes`) untuk kategori tersebut (Req 2.2). Bila ada
 * kategori yang belum dipilih atau nilainya tidak terdaftar, validasi menolak
 * pengiriman dan menyebutkan kategori yang belum lengkap/invalid (Req 2.5).
 *
 * ## Kontrak penamaan field (dipakai juga oleh halaman form task 7.5)
 *
 * Skema: `{parent}_{suffix}` dengan parent ∈ {father, mother} dan suffix ∈
 * {blood, iris, hair, ear}. Delapan field yang divalidasi:
 *
 *   father_blood, father_iris, father_hair, father_ear,
 *   mother_blood, mother_iris, mother_hair, mother_ear
 *
 * Suffix dipetakan ke {@see PhenotypeCategory} melalui {@see self::CATEGORY_FIELDS}.
 * Atribut Hasil_Skrining_Orang_Tua (father_thalassemia / mother_thalassemia)
 * TIDAK divalidasi di sini karena berasal dari Hasil_Skrining Tahap 1
 * (read-only), bukan dari input pengguna; controller yang menambahkannya saat
 * membangun masukan Mesin_Naive_Bayes.
 */
class PredictionRequest extends FormRequest
{
    /**
     * Peta suffix field form -> kategori Fenotipe kanonik.
     *
     * Suffix menjaga nama field tetap ringkas & konsisten dengan kunci atribut
     * masukan Mesin_Naive_Bayes (father_blood, mother_iris, dst.), sementara
     * kategori dipakai untuk membatasi nilai valid dari Data_Fenotipe (Req 2.2).
     *
     * @var array<string,PhenotypeCategory>
     */
    public const CATEGORY_FIELDS = [
        'blood' => PhenotypeCategory::GolonganDarah,
        'iris' => PhenotypeCategory::WarnaIris,
        'hair' => PhenotypeCategory::TeksturRambut,
        'ear' => PhenotypeCategory::BentukCuping,
    ];

    /**
     * Orang tua yang fenotipenya diinput.
     *
     * @var list<string>
     */
    public const PARENTS = ['father', 'mother'];

    /**
     * Prediksi bersifat publik (tanpa autentikasi); guard alur ditangani oleh
     * middleware `screening.completed`.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi: 4 kategori Fenotipe wajib per orang tua, masing-masing
     * dibatasi nilai terdaftar pada Data_Fenotipe untuk kategorinya (Req 2.2, 2.5).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (self::CATEGORY_FIELDS as $suffix => $category) {
            $allowedValues = $this->allowedValuesFor($category);

            foreach (self::PARENTS as $parent) {
                $rules["{$parent}_{$suffix}"] = ['required', 'string', Rule::in($allowedValues)];
            }
        }

        return $rules;
    }

    /**
     * Nama field ramah-pengguna agar pesan kesalahan menyebut kategori yang
     * belum lengkap/invalid secara jelas (Req 2.5).
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $labels = ['father' => 'ayah', 'mother' => 'ibu'];

        $attributes = [];

        foreach (self::CATEGORY_FIELDS as $suffix => $category) {
            foreach (self::PARENTS as $parent) {
                $attributes["{$parent}_{$suffix}"] = "{$category->value} {$labels[$parent]}";
            }
        }

        return $attributes;
    }

    /**
     * Daftar nilai terdaftar pada Data_Fenotipe untuk satu kategori (Req 2.2).
     *
     * @return list<string>
     */
    private function allowedValuesFor(PhenotypeCategory $category): array
    {
        return Phenotype::query()
            ->where('category', $category->value)
            ->pluck('value')
            ->all();
    }
}
