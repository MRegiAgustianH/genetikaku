<?php

namespace App\Http\Requests\Admin;

use App\Domain\PhenotypeCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PhenotypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Otorisasi area admin ditangani oleh middleware `admin` pada grup route
     * (Req 9.3), sehingga request ini hanya memvalidasi data.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi entri Data_Fenotipe (Req 13.3).
     *
     * Kategori wajib dan harus salah satu nilai PhenotypeCategory yang valid;
     * nilai wajib diisi.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(PhenotypeCategory::class)],
            'value' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Pesan kesalahan validasi yang ramah pengguna (Req 13.3).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.required' => 'Kategori fenotipe wajib dipilih.',
            'category.enum' => 'Kategori fenotipe tidak valid.',
            'value.required' => 'Nilai fenotipe wajib diisi.',
        ];
    }
}
