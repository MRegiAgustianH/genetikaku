<?php

namespace App\Http\Controllers\Public;

use App\Domain\KnowledgeBaseRule as KnowledgeBaseRuleDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ScreeningRequest;
use App\Models\KnowledgeBaseRule;
use App\Models\MediaAsset;
use App\Models\ScreeningResult;
use App\Services\ScreeningEngine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Alur publik Tahap 1: Skrining Risiko Thalassemia Orang Tua (Req 1).
 *
 * - GET  /skrining  : tampilkan formulir Indikator_Skrining ayah & ibu.
 * - POST /skrining  : validasi, jalankan Mesin_Skrining untuk ayah & ibu,
 *                     simpan Hasil_Skrining, simpan id ke sesi, lalu lanjut
 *                     ke Tahap 2 (/prediksi).
 */
class ScreeningController extends Controller
{
    /**
     * Kunci sesi tempat id Hasil_Skrining disimpan untuk meneruskan state ke
     * Tahap 2 (Req 1.6, dipakai juga oleh guard EnsureScreeningCompleted).
     */
    public const SESSION_KEY = 'screening_result_id';

    /**
     * Tampilkan formulir skrining ayah & ibu (Req 1.1).
     *
     * Halaman React `public/screening` dibangun pada task 6.2; di sini kita
     * hanya menyediakan daftar indikator sebagai props.
     */
    public function show(): Response
    {
        $illustration = MediaAsset::query()
            ->where('key', 'screening_illustration')
            ->first();

        return Inertia::render('public/screening', [
            'indicators' => collect(ScreeningRequest::INDICATORS)
                ->map(fn (string $label, string $key): array => [
                    'key' => $key,
                    'label' => $label,
                ])
                ->values()
                ->all(),
            'illustration' => ($illustration && $illustration->path) ? [
                'url' => $illustration->url,
                'type' => $illustration->type,
            ] : null,
        ]);
    }

    /**
     * Proses pengiriman formulir skrining (Req 1.2, 1.5, 1.6).
     */
    public function store(ScreeningRequest $request, ScreeningEngine $engine): RedirectResponse
    {
        $validated = $request->validated();

        $rules = $this->loadRules();

        $fatherResult = $engine->classify($this->mapAnswers($validated['father'] ?? []), $rules);
        $motherResult = $engine->classify($this->mapAnswers($validated['mother'] ?? []), $rules);

        $result = ScreeningResult::query()->create([
            'father_name' => $validated['father_name'],
            'mother_name' => $validated['mother_name'],
            'father_result' => $fatherResult,
            'mother_result' => $motherResult,
        ]);

        $request->session()->put(self::SESSION_KEY, $result->id);

        return redirect('/prediksi');
    }

    /**
     * Muat Basis_Pengetahuan dari DB dan petakan ke DTO domain.
     *
     * @return list<KnowledgeBaseRuleDto>
     */
    private function loadRules(): array
    {
        return KnowledgeBaseRule::query()
            ->get(['indicator', 'weight', 'classification_mapping'])
            ->map(fn (KnowledgeBaseRule $rule): KnowledgeBaseRuleDto => KnowledgeBaseRuleDto::fromArray([
                'indicator' => $rule->indicator,
                'weight' => $rule->weight,
                'classification_mapping' => $rule->classification_mapping,
            ]))
            ->all();
    }

    /**
     * Petakan jawaban form (berkunci slug indikator) menjadi jawaban berkunci
     * nama indikator kanonik yang dikenali Mesin_Skrining.
     *
     * @param  array<string,mixed>  $answers
     * @return array<string,mixed>
     */
    private function mapAnswers(array $answers): array
    {
        $mapped = [];

        foreach (ScreeningRequest::INDICATORS as $key => $label) {
            $mapped[$label] = $answers[$key] ?? false;
        }

        return $mapped;
    }
}
