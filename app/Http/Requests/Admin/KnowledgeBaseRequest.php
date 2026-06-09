<?php

namespace App\Http\Requests\Admin;

use App\Domain\ScreeningCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi aturan Basis_Pengetahuan (Req 12.4).
 *
 * - indicator: nama indikator/ciri skrining (teks bebas). Admin dapat menambah
 *   indikator baru di luar daftar bawaan; unik per indikator.
 * - weight: bilangan bulat >= 0.
 * - classification_mapping: salah satu kategori Hasil_Skrining_Orang_Tua.
 */
class KnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'indicator' => [
                'required',
                'string',
                'max:255',
                Rule::unique('knowledge_base_rules', 'indicator')
                    ->ignore($this->route('basis_pengetahuan')),
            ],
            'weight' => ['required', 'integer', 'min:0'],
            'classification_mapping' => [
                'required',
                'string',
                Rule::in(array_column(ScreeningCategory::cases(), 'value')),
            ],
            'illustration' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:20480'],
            'remove_illustration' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'indicator.required' => 'Nama indikator wajib diisi.',
            'indicator.unique' => 'Sudah ada aturan untuk indikator ini.',
            'weight.required' => 'Bobot wajib diisi.',
            'weight.integer' => 'Bobot harus berupa angka bulat.',
            'weight.min' => 'Bobot tidak boleh kurang dari 0.',
            'classification_mapping.required' => 'Pemetaan klasifikasi wajib dipilih.',
            'classification_mapping.in' => 'Pemetaan klasifikasi harus salah satu dari: Normal, Carrier, atau Berisiko Tinggi.',
        ];
    }
}
