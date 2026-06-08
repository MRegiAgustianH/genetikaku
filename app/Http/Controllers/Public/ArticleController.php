<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    /**
     * Tampilkan daftar artikel yang dipublikasikan (Req 7.1).
     */
    public function index()
    {
        $articles = Article::query()
            ->where('status', 'published')
            ->orderByDesc('id')
            ->get(['title', 'slug', 'content', 'image_path'])
            ->map(fn (Article $article) => [
                'title' => $article->title,
                'slug' => $article->slug,
                'image_url' => $article->image_url,
                'excerpt' => Str::limit(strip_tags($article->content), 140),
            ])
            ->values();

        return Inertia::render('public/articles/index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Tampilkan detail satu artikel terpublikasi (Req 7.2).
     *
     * Jika artikel tidak ada atau berstatus draft, kembalikan halaman
     * "tidak ditemukan" dengan HTTP 404 (Req 7.3).
     */
    public function show(Request $request, string $slug)
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if ($article === null) {
            return Inertia::render('public/articles/not-found', [
                'slug' => $slug,
            ])->toResponse($request)->setStatusCode(404);
        }

        return Inertia::render('public/articles/show', [
            'article' => [
                'title' => $article->title,
                'content' => $article->content,
                'image_url' => $article->image_url,
            ],
        ]);
    }
}
