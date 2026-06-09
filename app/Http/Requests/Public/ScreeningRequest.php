<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


class ScreeningRequest extends FormRequest
{
   
    public const INDICATORS = [
        'riwayat_keluarga' => 'Riwayat keluarga Thalassemia',
        'riwayat_diagnosis' => 'Riwayat diagnosis Thalassemia',
        'riwayat_anemia' => 'Riwayat anemia',
        'kadar_hb_rendah' => 'Kadar Hb rendah',
        'riwayat_transfusi' => 'Riwayat transfusi darah',
        'gejala_pendukung' => 'Gejala pendukung lainnya',
    ];

    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        $rules = [
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
        ];

        foreach (array_keys(\App\Support\ScreeningIndicators::map()) as $key) {
            $rules["father.{$key}"] = ['required', 'boolean'];
            $rules["mother.{$key}"] = ['required', 'boolean'];
        }

        return $rules;
    }

   
    public function attributes(): array
    {
        $attributes = [
            'father_name' => 'nama ayah',
            'mother_name' => 'nama ibu',
        ];

        foreach (\App\Support\ScreeningIndicators::map() as $key => $label) {
            $attributes["father.{$key}"] = "indikator ayah: {$label}";
            $attributes["mother.{$key}"] = "indikator ibu: {$label}";
        }

        return $attributes;
    }
}
