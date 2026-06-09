<?php

namespace App\Http\Controllers\Admin;

use App\Domain\PhenotypeCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PhenotypeRequest;
use App\Models\Phenotype;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PhenotypeController extends Controller
{

    public function index(): Response
    {
        $phenotypes = Phenotype::query()
            ->orderBy('category')
            ->orderBy('value')
            ->get(['id', 'category', 'value', 'illustration_path'])
            ->map(fn (Phenotype $phenotype): array => [
                'id' => $phenotype->id,
                'category' => $phenotype->category->value,
                'value' => $phenotype->value,
                'illustration_url' => $phenotype->illustration_url,
                'illustration_type' => $phenotype->illustration_type,
            ])
            ->values();

        return Inertia::render('admin/phenotypes/index', [
            'phenotypes' => $phenotypes,
            'categories' => $this->categoryOptions(),
        ]);
    }

    
    public function create(): Response
    {
        return Inertia::render('admin/phenotypes/create', [
            'categories' => $this->categoryOptions(),
        ]);
    }

    
    public function store(PhenotypeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $phenotype = new Phenotype();
        $phenotype->category = $data['category'];
        $phenotype->value = $data['value'];

        if ($request->hasFile('illustration')) {
            $phenotype->illustration_path = $request->file('illustration')->store('phenotypes', 'public');
        }

        $phenotype->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data fenotipe berhasil ditambahkan.']);

        return to_route('admin.fenotipe.index');
    }

   
    public function edit(Phenotype $fenotipe): Response
    {
        return Inertia::render('admin/phenotypes/edit', [
            'phenotype' => [
                'id' => $fenotipe->id,
                'category' => $fenotipe->category->value,
                'value' => $fenotipe->value,
                'illustration_url' => $fenotipe->illustration_url,
                'illustration_type' => $fenotipe->illustration_type,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    
    public function update(PhenotypeRequest $request, Phenotype $fenotipe): RedirectResponse
    {
        $data = $request->validated();

        $fenotipe->category = $data['category'];
        $fenotipe->value = $data['value'];

        if ($request->hasFile('illustration')) {
            if ($fenotipe->illustration_path) {
                Storage::disk('public')->delete($fenotipe->illustration_path);
            }
            $fenotipe->illustration_path = $request->file('illustration')->store('phenotypes', 'public');
        } elseif ($request->boolean('remove_illustration') && $fenotipe->illustration_path) {
            Storage::disk('public')->delete($fenotipe->illustration_path);
            $fenotipe->illustration_path = null;
        }

        $fenotipe->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data fenotipe berhasil diperbarui.']);

        return to_route('admin.fenotipe.index');
    }

    
    public function destroy(Phenotype $fenotipe): RedirectResponse
    {
        if ($fenotipe->illustration_path) {
            Storage::disk('public')->delete($fenotipe->illustration_path);
        }

        $fenotipe->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data fenotipe berhasil dihapus.']);

        return to_route('admin.fenotipe.index');
    }

    
    private function categoryOptions(): array
    {
        return array_map(
            fn (PhenotypeCategory $category): string => $category->value,
            PhenotypeCategory::cases(),
        );
    }
}
