<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi formulir skrining Tahap 1 (Req 1.1, 1.4).
 *
 * Memvalidasi nama ayah & ibu serta keenam Indikator_Skrining wajib untuk
 * MASING-MASING orang tua. Bila ada indikator yang belum terisi, validasi
 * menolak pengiriman dan menyebutkan field indikator yang belum lengkap.
 */
class ScreeningRequest extends FormRequest
{
    /**
     * Peta kunci field form -> nama Indikator_Skrining kanonik (Req 1.1).
     *
     * Nilai (label) harus cocok dengan kolom `knowledge_base_rules.indicator`
     * agar {@see \App\Services\ScreeningEngine} dapat menemukan jawaban yang
     * sesuai dengan setiap aturan Basis_Pengetahuan.
     *
     * @var array<string,string>
     */
    public const INDICATORS = [
        'riwayat_keluarga' => 'Riwayat keluarga Thalassemia',
        'riwayat_diagnosis' => 'Riwayat diagnosis Thalassemia',
        'riwayat_anemia' => 'Riwayat anemia',
        'kadar_hb_rendah' => 'Kadar Hb rendah',
        'riwayat_transfusi' => 'Riwayat transfusi darah',
        'gejala_pendukung' => 'Gejala pendukung lainnya',
    ];

    /**
     * Skrining bersifat publik (tanpa autentikasi).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi: nama wajib + 6 indikator wajib per orang tua (Req 1.4).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
        ];

        foreach (array_keys(self::INDICATORS) as $key) {
            $rules["father.{$key}"] = ['required', 'boolean'];
            $rules["mother.{$key}"] = ['required', 'boolean'];
        }

        return $rules;
    }

    /**
     * Nama field yang ramah-pengguna agar pesan kesalahan menyebut indikator
     * yang belum lengkap secara jelas (Req 1.4).
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'father_name' => 'nama ayah',
            'mother_name' => 'nama ibu',
        ];

        foreach (self::INDICATORS as $key => $label) {
            $attributes["father.{$key}"] = "indikator ayah: {$label}";
            $attributes["mother.{$key}"] = "indikator ibu: {$label}";
        }

        return $attributes;
    }
}
