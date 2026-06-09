<?php

namespace App\Http\Controllers\Admin;

use App\Domain\ScreeningCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgeBaseRequest;
use App\Http\Requests\Public\ScreeningRequest;
use App\Models\KnowledgeBaseRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manajemen Basis_Pengetahuan skrining Thalassemia (Req 12).
 *
 * Admin mengelola aturan skrining: indikator/ciri, bobot, dan kategori yang
 * diindikasikan. Indikator bersifat dinamis — admin dapat menambah indikator
 * baru, dan indikator yang ada otomatis tampil pada formulir skrining publik.
 * Nilai awal berasal dari wawancara pakar (KnowledgeBaseSeeder). Perubahan
 * langsung dipakai {@see \App\Services\ScreeningEngine} karena ScreeningController
 * membaca aturan dari DB.
 */
class KnowledgeBaseController extends Controller
{
    public function index(): Response
    {
        $rules = KnowledgeBaseRule::query()
            ->orderBy('id')
            ->get(['id', 'indicator', 'weight', 'classification_mapping', 'illustration_path'])
            ->map(fn (KnowledgeBaseRule $rule): array => [
                'id' => $rule->id,
                'indicator' => $rule->indicator,
                'weight' => $rule->weight,
                'classification_mapping' => $rule->classification_mapping,
                'illustration_url' => $rule->illustration_url,
                'illustration_type' => $rule->illustration_type,
            ])
            ->values();

        return Inertia::render('admin/knowledge-base/index', [
            'rules' => $rules,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/knowledge-base/create', [
            'indicatorSuggestions' => array_values(ScreeningRequest::INDICATORS),
            'classificationOptions' => array_column(ScreeningCategory::cases(), 'value'),
        ]);
    }

    public function store(KnowledgeBaseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $rule = new KnowledgeBaseRule();
        $rule->slug = $this->uniqueSlug($data['indicator']);
        $rule->indicator = $data['indicator'];
        $rule->weight = $data['weight'];
        $rule->classification_mapping = $data['classification_mapping'];

        if ($request->hasFile('illustration')) {
            $rule->illustration_path = $request->file('illustration')->store('knowledge-base', 'public');
        }

        $rule->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aturan basis pengetahuan berhasil ditambahkan.']);

        return to_route('admin.basis-pengetahuan.index');
    }

    public function edit(KnowledgeBaseRule $basis_pengetahuan): Response
    {
        return Inertia::render('admin/knowledge-base/edit', [
            'rule' => [
                'id' => $basis_pengetahuan->id,
                'indicator' => $basis_pengetahuan->indicator,
                'weight' => $basis_pengetahuan->weight,
                'classification_mapping' => $basis_pengetahuan->classification_mapping,
                'illustration_url' => $basis_pengetahuan->illustration_url,
                'illustration_type' => $basis_pengetahuan->illustration_type,
            ],
            'indicatorSuggestions' => array_values(ScreeningRequest::INDICATORS),
            'classificationOptions' => array_column(ScreeningCategory::cases(), 'value'),
        ]);
    }

    public function update(KnowledgeBaseRequest $request, KnowledgeBaseRule $basis_pengetahuan): RedirectResponse
    {
        $data = $request->validated();

        $basis_pengetahuan->indicator = $data['indicator'];
        $basis_pengetahuan->weight = $data['weight'];
        $basis_pengetahuan->classification_mapping = $data['classification_mapping'];

        if ($data['indicator'] !== $basis_pengetahuan->getOriginal('indicator')) {
            $basis_pengetahuan->slug = $this->uniqueSlug($data['indicator'], $basis_pengetahuan->id);
        }

        if ($request->hasFile('illustration')) {
            if ($basis_pengetahuan->illustration_path) {
                Storage::disk('public')->delete($basis_pengetahuan->illustration_path);
            }
            $basis_pengetahuan->illustration_path = $request->file('illustration')->store('knowledge-base', 'public');
        } elseif ($request->boolean('remove_illustration') && $basis_pengetahuan->illustration_path) {
            Storage::disk('public')->delete($basis_pengetahuan->illustration_path);
            $basis_pengetahuan->illustration_path = null;
        }

        $basis_pengetahuan->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aturan basis pengetahuan berhasil diperbarui.']);

        return to_route('admin.basis-pengetahuan.index');
    }

    public function destroy(KnowledgeBaseRule $basis_pengetahuan): RedirectResponse
    {
        $basis_pengetahuan->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aturan basis pengetahuan berhasil dihapus.']);

        return to_route('admin.basis-pengetahuan.index');
    }

    /**
     * Bangun slug unik (dengan sufiks numerik bila perlu) dari label indikator.
     */
    private function uniqueSlug(string $indicator, ?int $ignoreId = null): string
    {
        $base = Str::slug($indicator, '_');

        if ($base === '') {
            $base = 'indikator';
        }

        $slug = $base;
        $suffix = 1;

        while (
            KnowledgeBaseRule::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'_'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
