<?php

namespace App\Http\Controllers\Admin;

use App\Domain\ScreeningCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgeBaseRequest;
use App\Models\KnowledgeBaseRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Manajemen Basis_Pengetahuan skrining Thalassemia (Req 12).
 *
 * Seluruh operasi tulis (store/update/destroy) dibungkus transaksi database
 * agar bila terjadi kegagalan teknis, perubahan di-rollback dan data
 * sebelumnya tetap utuh, lalu Admin menerima notifikasi kegagalan
 * (flash message) (Req 12.3).
 */
class KnowledgeBaseController extends Controller
{
    /**
     * Tampilkan daftar aturan Basis_Pengetahuan beserta bobot dan
     * pemetaan klasifikasinya (Req 12.1).
     */
    public function index(): Response
    {
        $rules = KnowledgeBaseRule::query()
            ->orderBy('id')
            ->get(['id', 'indicator', 'weight', 'classification_mapping']);

        return Inertia::render('admin/knowledge-base/index', [
            'rules' => $rules,
        ]);
    }

    /**
     * Tampilkan formulir pembuatan aturan baru.
     */
    public function create(): Response
    {
        return Inertia::render('admin/knowledge-base/create', [
            'classificationOptions' => $this->classificationOptions(),
        ]);
    }

    /**
     * Simpan aturan baru di dalam transaksi (Req 12.2, 12.3, 12.4).
     */
    public function store(KnowledgeBaseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data): void {
                KnowledgeBaseRule::create($data);
            });
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan aturan Basis_Pengetahuan', ['exception' => $e]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan aturan basis pengetahuan. Perubahan dibatalkan dan data sebelumnya dipertahankan.');
        }

        return redirect()
            ->route('admin.basis-pengetahuan.index')
            ->with('success', 'Aturan basis pengetahuan berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir ubah aturan.
     */
    public function edit(KnowledgeBaseRule $basis_pengetahuan): Response
    {
        return Inertia::render('admin/knowledge-base/edit', [
            'rule' => $basis_pengetahuan->only(['id', 'indicator', 'weight', 'classification_mapping']),
            'classificationOptions' => $this->classificationOptions(),
        ]);
    }

    /**
     * Perbarui aturan di dalam transaksi (Req 12.2, 12.3, 12.4).
     */
    public function update(KnowledgeBaseRequest $request, KnowledgeBaseRule $basis_pengetahuan): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($basis_pengetahuan, $data): void {
                $basis_pengetahuan->update($data);
            });
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui aturan Basis_Pengetahuan', ['exception' => $e]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan aturan basis pengetahuan. Perubahan dibatalkan dan data sebelumnya dipertahankan.');
        }

        return redirect()
            ->route('admin.basis-pengetahuan.index')
            ->with('success', 'Aturan basis pengetahuan berhasil diperbarui.');
    }

    /**
     * Hapus aturan di dalam transaksi (Req 12.2, 12.3).
     */
    public function destroy(KnowledgeBaseRule $basis_pengetahuan): RedirectResponse
    {
        try {
            DB::transaction(function () use ($basis_pengetahuan): void {
                $basis_pengetahuan->delete();
            });
        } catch (Throwable $e) {
            Log::error('Gagal menghapus aturan Basis_Pengetahuan', ['exception' => $e]);

            return back()
                ->with('error', 'Gagal menghapus aturan basis pengetahuan. Perubahan dibatalkan dan data sebelumnya dipertahankan.');
        }

        return redirect()
            ->route('admin.basis-pengetahuan.index')
            ->with('success', 'Aturan basis pengetahuan berhasil dihapus.');
    }

    /**
     * Daftar nilai pemetaan klasifikasi yang valid untuk pilihan formulir.
     *
     * @return list<string>
     */
    private function classificationOptions(): array
    {
        return array_column(ScreeningCategory::cases(), 'value');
    }
}
