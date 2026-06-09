<?php

namespace App\Http\Requests\Admin;

use App\Domain\PhenotypeCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PhenotypeRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(PhenotypeCategory::class)],
            'value' => ['required', 'string', 'max:255'],
            'illustration' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:20480'],
            'remove_illustration' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Kategori fenotipe wajib dipilih.',
            'category.enum' => 'Kategori fenotipe tidak valid.',
            'value.required' => 'Nilai fenotipe wajib diisi.',
        ];
    }
}
