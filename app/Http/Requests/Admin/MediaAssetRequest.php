<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MediaAssetRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:20480'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
