<?php

namespace App\Http\Controllers\Admin;

use App\Domain\PhenotypeCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PhenotypeRequest;
use App\Models\Phenotype;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PhenotypeController extends Controller
{
    /**
     * Tampilkan daftar entri Data_Fenotipe beserta kategori dan nilainya (Req 13.1).
     */
    public function index(): Response
    {
        $phenotypes = Phenotype::query()
            ->orderBy('category')
            ->orderBy('value')
            ->get(['id', 'category', 'value'])
            ->map(fn (Phenotype $phenotype): array => [
                'id' => $phenotype->id,
                'category' => $phenotype->category->value,
                'value' => $phenotype->value,
            ])
            ->values();

        return Inertia::render('admin/phenotypes/index', [
            'phenotypes' => $phenotypes,
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Tampilkan formulir pembuatan entri Data_Fenotipe baru.
     */
    public function create(): Response
    {
        return Inertia::render('admin/phenotypes/create', [
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Simpan entri Data_Fenotipe baru (Req 13.2, 13.3).
     *
     * Perubahan langsung tercermin pada opsi formulir prediksi Tahap 2 karena
     * opsi tersebut dibaca dari tabel `phenotypes` (Req 13.2).
     */
    public function store(PhenotypeRequest $request): RedirectResponse
    {
        Phenotype::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data fenotipe berhasil ditambahkan.']);

        return to_route('admin.fenotipe.index');
    }

    /**
     * Tampilkan formulir pengubahan entri Data_Fenotipe.
     */
    public function edit(Phenotype $fenotipe): Response
    {
        return Inertia::render('admin/phenotypes/edit', [
            'phenotype' => [
                'id' => $fenotipe->id,
                'category' => $fenotipe->category->value,
                'value' => $fenotipe->value,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Perbarui entri Data_Fenotipe (Req 13.2, 13.3).
     */
    public function update(PhenotypeRequest $request, Phenotype $fenotipe): RedirectResponse
    {
        $fenotipe->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data fenotipe berhasil diperbarui.']);

        return to_route('admin.fenotipe.index');
    }

    /**
     * Hapus entri Data_Fenotipe (Req 13.2).
     */
    public function destroy(Phenotype $fenotipe): RedirectResponse
    {
        $fenotipe->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data fenotipe berhasil dihapus.']);

        return to_route('admin.fenotipe.index');
    }

    /**
     * Daftar kategori fenotipe yang valid untuk ditampilkan pada formulir.
     *
     * @return list<string>
     */
    private function categoryOptions(): array
    {
        return array_map(
            fn (PhenotypeCategory $category): string => $category->value,
            PhenotypeCategory::cases(),
        );
    }
}
