<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    /**
     * Tampilkan seluruh artikel (semua status) pada daftar admin (Req 10.1).
     */
    public function index(): Response
    {
        $articles = Article::query()
            ->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'status'])
            ->map(fn (Article $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'status' => $article->status,
            ])
            ->values();

        return Inertia::render('admin/articles/index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Tampilkan formulir pembuatan artikel baru.
     */
    public function create(): Response
    {
        return Inertia::render('admin/articles/create');
    }

    /**
     * Simpan artikel baru (Req 10.1).
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('articles', 'public');
        }

        Article::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Artikel berhasil dibuat.']);

        return to_route('admin.artikel.index');
    }

    /**
     * Tampilkan formulir penyuntingan artikel.
     */
    public function edit(Article $artikel): Response
    {
        return Inertia::render('admin/articles/edit', [
            'article' => [
                'id' => $artikel->id,
                'title' => $artikel->title,
                'content' => $artikel->content,
                'status' => $artikel->status,
                'image_url' => $artikel->image_url,
            ],
        ]);
    }

    /**
     * Perbarui artikel yang ada (Req 10.2).
     */
    public function update(ArticleRequest $request, Article $artikel): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($data['title'] !== $artikel->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $artikel->id);
        }

        if ($request->hasFile('image')) {
            if ($artikel->image_path) {
                Storage::disk('public')->delete($artikel->image_path);
            }

            $data['image_path'] = $request->file('image')->store('articles', 'public');
        }

        $artikel->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Artikel berhasil diperbarui.']);

        return to_route('admin.artikel.index');
    }

    /**
     * Hapus artikel (Req 10.3).
     */
    public function destroy(Article $artikel): RedirectResponse
    {
        $artikel->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Artikel berhasil dihapus.']);

        return to_route('admin.artikel.index');
    }

    /**
     * Buat slug unik dari judul, menambahkan sufiks numerik bila perlu.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'artikel';
        }

        $slug = $base;
        $suffix = 1;

        while (
            Article::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
