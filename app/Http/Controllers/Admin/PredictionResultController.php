<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredictionResult;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;


class PredictionResultController extends Controller
{
    
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
