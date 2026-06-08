<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredictionResult;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manajemen Hasil_Prediksi tersimpan (Req 15).
 *
 * Read-only kecuali penghapusan: index menampilkan daftar Hasil_Prediksi
 * beserta hasil fisik, risiko Thalassemia, dan probabilitas (Req 15.1);
 * show menampilkan detail lengkap termasuk Hasil_Skrining terkait (Req 15.2);
 * destroy menghapus record dari daftar (Req 15.3).
 */
class PredictionResultController extends Controller
{
    /**
     * Daftar Hasil_Prediksi tersimpan beserta hasil fisik, hasil Thalassemia,
     * dan probabilitas (Req 15.1). Hasil_Skrining di-eager-load untuk konteks.
     */
    public function index(): Response
    {
        $results = PredictionResult::query()
            ->with('screeningResult')
            ->latest('id')
            ->get()
            ->map(static fn (PredictionResult $result): array => [
                'id' => $result->id,
                'physical_result' => $result->physical_result,
                'thalassemia_risk' => $result->thalassemia_risk->value,
                'probabilities' => $result->probabilities,
                'created_at' => $result->created_at?->toIso8601String(),
                'screening_result' => $result->screeningResult === null ? null : [
                    'id' => $result->screeningResult->id,
                    'father_name' => $result->screeningResult->father_name,
                    'mother_name' => $result->screeningResult->mother_name,
                    'father_result' => $result->screeningResult->father_result->value,
                    'mother_result' => $result->screeningResult->mother_result->value,
                ],
            ]);

        return Inertia::render('admin/prediction-results/index', [
            'results' => $results,
        ]);
    }

    /**
     * Detail lengkap satu Hasil_Prediksi termasuk Hasil_Skrining terkait
     * (Req 15.2).
     */
    public function show(PredictionResult $hasilPrediksi): Response
    {
        $hasilPrediksi->load('screeningResult');

        return Inertia::render('admin/prediction-results/show', [
            'result' => [
                'id' => $hasilPrediksi->id,
                'physical_result' => $hasilPrediksi->physical_result,
                'thalassemia_risk' => $hasilPrediksi->thalassemia_risk->value,
                'probabilities' => $hasilPrediksi->probabilities,
                'created_at' => $hasilPrediksi->created_at?->toIso8601String(),
                'screening_result' => $hasilPrediksi->screeningResult === null ? null : [
                    'id' => $hasilPrediksi->screeningResult->id,
                    'father_name' => $hasilPrediksi->screeningResult->father_name,
                    'mother_name' => $hasilPrediksi->screeningResult->mother_name,
                    'father_result' => $hasilPrediksi->screeningResult->father_result->value,
                    'mother_result' => $hasilPrediksi->screeningResult->mother_result->value,
                ],
            ],
        ]);
    }

    /**
     * Hapus satu Hasil_Prediksi dari daftar (Req 15.3).
     */
    public function destroy(PredictionResult $hasilPrediksi): RedirectResponse
    {
        $hasilPrediksi->delete();

        return redirect()
            ->route('admin.hasil-prediksi.index')
            ->with('success', 'Hasil Prediksi berhasil dihapus.');
    }
}
