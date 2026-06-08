<?php

use App\Models\Article;
use Eris\Generator;
use Eris\TestTrait;
use Inertia\Testing\AssertableInertia;
use Tests\RefreshDatabaseWithoutSeeding;

uses(TestTrait::class, RefreshDatabaseWithoutSeeding::class);

/*
 * Property 16 menguji visibilitas artikel pada tampilan publik di level
 * rute/HTTP + Inertia. Untuk sembarang himpunan artikel berstatus campuran
 * (draft dan published):
 *   - Daftar publik (route 'artikel.index', prop 'articles') HANYA memuat
 *     artikel berstatus 'published' (dicocokkan berdasarkan himpunan slug),
 *     dan tidak satu pun draft muncul.
 *   - Detail publik (route 'artikel.show', slug) untuk artikel draft ATAU slug
 *     yang tidak ada menghasilkan halaman 'public/articles/not-found' dengan
 *     HTTP 404.
 *
 * Prefiks unik PROP16_ dipakai untuk simbol lingkup-berkas guna menghindari
 * tabrakan simbol global dengan berkas tes lain.
 */

/**
 * Generator jumlah artikel published & draft, dibatasi kecil (0-5 masing-masing)
 * agar pembuatan baris DB pada >=100 iterasi tetap berkinerja.
 *
 * @return \Eris\Generator
 */
function prop16CountsGenerator(): callable
{
    return Generator\tuple(
        Generator\choose(0, 5),
        Generator\choose(0, 5),
    );
}

// Feature: genetikaku-expert-system, Property 16: Tampilan publik artikel hanya memuat artikel terpublikasi
it('daftar artikel publik hanya memuat artikel terpublikasi', function () {
    // Nonaktifkan Vite agar render Inertia tidak bergantung pada aset terbuild.
    $this->withoutVite();

    $this->forAll(
        prop16CountsGenerator(),
    )->then(function (array $counts) {
        [$publishedCount, $draftCount] = $counts;

        // Mulai dari state bersih tiap iterasi agar himpunan terkontrol.
        Article::query()->delete();

        $published = $publishedCount > 0
            ? Article::factory()->count($publishedCount)->published()->create()
            : collect();

        if ($draftCount > 0) {
            Article::factory()->count($draftCount)->draft()->create();
        }

        $expectedPublishedSlugs = $published->pluck('slug')->sort()->values()->all();

        $response = $this->get(route('artikel.index'));
        $response->assertOk();

        $response->assertInertia(function (AssertableInertia $page) use ($expectedPublishedSlugs) {
            $page->component('public/articles/index');

            $articles = $page->toArray()['props']['articles'] ?? [];
            $actualSlugs = collect($articles)->pluck('slug')->sort()->values()->all();

            // Tepat himpunan slug published — tidak kurang, tidak lebih (tanpa draft).
            expect($actualSlugs)->toBe($expectedPublishedSlugs);
        });
    });
});

// Feature: genetikaku-expert-system, Property 16: Tampilan publik artikel hanya memuat artikel terpublikasi
it('detail publik untuk artikel draft menghasilkan halaman tidak ditemukan (404)', function () {
    $this->withoutVite();

    $this->forAll(
        Generator\choose(1, 5),
    )->then(function (int $draftCount) {
        Article::query()->delete();

        $drafts = Article::factory()->count($draftCount)->draft()->create();
        $slug = $drafts->random()->slug;

        $response = $this->get(route('artikel.show', $slug));

        $response->assertStatus(404);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/articles/not-found')
        );
    });
});

// Feature: genetikaku-expert-system, Property 16: Tampilan publik artikel hanya memuat artikel terpublikasi
it('detail publik untuk slug yang tidak ada menghasilkan halaman tidak ditemukan (404)', function () {
    $this->withoutVite();

    $this->forAll(
        Generator\suchThat(
            fn (string $s) => trim($s) !== '' && ! str_contains($s, '/'),
            Generator\string(),
        ),
    )->then(function (string $randomSlug) {
        Article::query()->delete();

        // Pastikan slug benar-benar tidak ada di DB.
        $slug = 'prop16-missing-'.md5($randomSlug);

        $response = $this->get(route('artikel.show', $slug));

        $response->assertStatus(404);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/articles/not-found')
        );
    });
});
