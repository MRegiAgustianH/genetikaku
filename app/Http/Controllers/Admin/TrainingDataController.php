<?php

namespace App\Http\Controllers\Admin;

use App\Domain\PhenotypeCategory;
use App\Domain\ScreeningCategory;
use App\Domain\ThalassemiaRisk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrainingDataRequest;
use App\Models\Phenotype;
use App\Models\TrainingData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manajemen Data_Latih Naive Bayes (Req 14).
 *
 * Resourceful: index, create, store, edit, update, destroy. Penyimpanan dan
 * pembaruan divalidasi oleh App\Http\Requests\Admin\TrainingDataRequest yang
 * menolak nilai di luar Data_Fenotipe/kategori Hasil_Skrining_Orang_Tua
 * (Req 14.3).
 */
class TrainingDataController extends Controller
{
    /**
     * Daftar baris Data_Latih beserta atribut ayah/ibu, status Thalassemia,
     * dan prediksi bayi (Req 14.1).
     */
    public function index(): Response
    {
        $rows = TrainingData::query()
            ->latest('id')
            ->get();

        return Inertia::render('admin/training-data/index', [
            'rows' => $rows,
        ]);
    }

    /**
     * Formulir pembuatan baris Data_Latih baru.
     */
    public function create(): Response
    {
        return Inertia::render('admin/training-data/create', [
            'phenotypeOptions' => $this->phenotypeOptions(),
            'screeningOptions' => $this->screeningOptions(),
            'riskOptions' => $this->riskOptions(),
        ]);
    }

    /**
     * Simpan baris Data_Latih baru (Req 14.2, 14.3).
     */
    public function store(TrainingDataRequest $request): RedirectResponse
    {
        TrainingData::create($request->validated());

        return redirect()
            ->route('admin.data-latih.index')
            ->with('success', 'Baris Data Latih berhasil ditambahkan.');
    }

    /**
     * Formulir perubahan baris Data_Latih.
     */
    public function edit(TrainingData $dataLatih): Response
    {
        return Inertia::render('admin/training-data/edit', [
            'row' => $dataLatih,
            'phenotypeOptions' => $this->phenotypeOptions(),
            'screeningOptions' => $this->screeningOptions(),
            'riskOptions' => $this->riskOptions(),
        ]);
    }

    /**
     * Perbarui baris Data_Latih (Req 14.2, 14.3).
     */
    public function update(TrainingDataRequest $request, TrainingData $dataLatih): RedirectResponse
    {
        $dataLatih->update($request->validated());

        return redirect()
            ->route('admin.data-latih.index')
            ->with('success', 'Baris Data Latih berhasil diperbarui.');
    }

    /**
     * Hapus baris Data_Latih (Req 14.2).
     */
    public function destroy(TrainingData $dataLatih): RedirectResponse
    {
        $dataLatih->delete();

        return redirect()
            ->route('admin.data-latih.index')
            ->with('success', 'Baris Data Latih berhasil dihapus.');
    }

    /**
     * Opsi nilai fenotipe per kategori dari Data_Fenotipe terkini.
     *
     * @return array<string, list<string>>
     */
    private function phenotypeOptions(): array
    {
        $options = [];

        foreach (PhenotypeCategory::cases() as $category) {
            $options[$category->value] = [];
        }

        foreach (Phenotype::query()->orderBy('value')->get(['category', 'value']) as $phenotype) {
            $category = $phenotype->category instanceof PhenotypeCategory
                ? $phenotype->category->value
                : (string) $phenotype->category;

            $options[$category][] = $phenotype->value;
        }

        return $options;
    }

    /**
     * Opsi kategori Hasil_Skrining_Orang_Tua.
     *
     * @return list<string>
     */
    private function screeningOptions(): array
    {
        return array_map(
            static fn (ScreeningCategory $c): string => $c->value,
            ScreeningCategory::cases(),
        );
    }

    /**
     * Opsi klasifikasi Risiko_Thalassemia_Bayi.
     *
     * @return list<string>
     */
    private function riskOptions(): array
    {
        return array_map(
            static fn (ThalassemiaRisk $r): string => $r->value,
            ThalassemiaRisk::cases(),
        );
    }
}
