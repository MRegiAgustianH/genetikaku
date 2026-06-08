<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutRequest;
use App\Models\AboutPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    /**
     * Tampilkan formulir penyuntingan halaman Tentang.
     *
     * Memuat satu record AboutPage yang dikelola Admin. Jika belum ada konten,
     * formulir dirender dengan nilai kosong agar Admin dapat membuat konten awal.
     */
    public function edit(): Response
    {
        $about = AboutPage::query()->first();

        return Inertia::render('admin/about/edit', [
            'about' => [
                'title' => $about->title ?? '',
                'content' => $about->content ?? '',
                'image_url' => $about?->image_url,
            ],
        ]);
    }

    /**
     * Simpan perubahan halaman Tentang (Req 11.1, 11.2).
     *
     * Validasi judul/konten wajib ditangani AboutRequest. Karena hanya ada satu
     * record Tentang, gunakan updateOrCreate sehingga record dibuat saat pertama
     * kali dan diperbarui pada penyuntingan berikutnya. Gambar opsional: bila
     * diunggah, berkas lama dihapus dari disk publik terlebih dahulu.
     */
    public function update(AboutRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        $about = AboutPage::query()->first();

        if ($request->hasFile('image')) {
            if ($about?->image_path) {
                Storage::disk('public')->delete($about->image_path);
            }

            $data['image_path'] = $request->file('image')->store('about', 'public');
        }

        AboutPage::query()->updateOrCreate(
            ['id' => $about?->id],
            $data,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Halaman Tentang berhasil diperbarui.']);

        return to_route('admin.tentang.edit');
    }
}
