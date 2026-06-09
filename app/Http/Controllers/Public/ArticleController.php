<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    
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

        $related = Article::query()
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->orderByDesc('id')
            ->limit(4)
            ->get(['title', 'slug'])
            ->map(fn (Article $item) => [
                'title' => $item->title,
                'slug' => $item->slug,
            ])
            ->values();

        return Inertia::render('public/articles/show', [
            'article' => [
                'title' => $article->title,
                'summary' => $article->summary,
                'content' => $article->content,
                'image_url' => $article->image_url,
                'published_at' => optional($article->created_at)->translatedFormat('d F Y'),
            ],
            'related' => $related,
        ]);
    }
}
