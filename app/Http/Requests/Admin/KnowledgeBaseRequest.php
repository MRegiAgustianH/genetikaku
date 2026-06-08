<?php

namespace App\Http\Requests\Admin;

use App\Domain\ScreeningCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KnowledgeBaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Otorisasi area admin ditangani oleh middleware `admin`
     * (EnsureUserIsAdmin) pada grup route, sehingga di sini selalu true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Field wajib untuk aturan Basis_Pengetahuan (Req 12.4):
     * - indicator: nama Indikator_Skrining (string, wajib)
     * - weight: bobot indikator (integer >= 0, wajib)
     * - classification_mapping: pemetaan klasifikasi, harus salah satu kategori
     *   Hasil_Skrining_Orang_Tua yang valid (Normal | Carrier | Berisiko Tinggi)
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'indicator' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:0'],
            'classification_mapping' => [
                'required',
                'string',
                Rule::in(array_column(ScreeningCategory::cases(), 'value')),
            ],
        ];
    }

    /**
     * Pesan kesalahan validasi berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'indicator.required' => 'Nama indikator wajib diisi.',
            'weight.required' => 'Bobot indikator wajib diisi.',
            'weight.integer' => 'Bobot indikator harus berupa angka bulat.',
            'weight.min' => 'Bobot indikator tidak boleh kurang dari 0.',
            'classification_mapping.required' => 'Pemetaan klasifikasi wajib diisi.',
            'classification_mapping.in' => 'Pemetaan klasifikasi harus salah satu dari: Normal, Carrier, atau Berisiko Tinggi.',
        ];
    }
}
