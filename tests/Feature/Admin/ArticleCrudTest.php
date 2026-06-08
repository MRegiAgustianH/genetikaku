<?php

use App\Models\Article;
use App\Models\User;

/*
 * Feature test CRUD artikel pada area admin (Req 10.1, 10.2, 10.3, 10.4).
 *
 * Suite 'Feature' sudah menerapkan RefreshDatabaseWithoutSeeding secara global
 * melalui tests/Pest.php, sehingga tiap tes berjalan terisolasi.
 */

/**
 * Buat pengguna dengan peran admin untuk mengakses grup route ['auth','admin'].
 */
function articleCrudAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

// Req 10.1: Admin dapat membuat artikel baru melalui store.
it('membuat artikel baru saat store dengan data valid', function () {
    $admin = articleCrudAdmin();

    $payload = [
        'title' => 'Mengenal Thalassemia',
        'content' => 'Konten edukatif tentang thalassemia yang cukup panjang.',
        'status' => 'published',
    ];

    $response = $this->actingAs($admin)->post(route('admin.artikel.store'), $payload);

    $response->assertRedirect(route('admin.artikel.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('articles', [
        'title' => 'Mengenal Thalassemia',
        'content' => 'Konten edukatif tentang thalassemia yang cukup panjang.',
        'status' => 'published',
        'slug' => 'mengenal-thalassemia',
    ]);
});

// Req 10.2: Admin dapat memperbarui judul dan konten artikel.
it('memperbarui judul dan konten artikel saat update', function () {
    $admin = articleCrudAdmin();

    $article = Article::factory()->create([
        'title' => 'Judul Lama',
        'slug' => 'judul-lama',
        'content' => 'Konten lama.',
        'status' => 'draft',
    ]);

    $payload = [
        'title' => 'Judul Baru',
        'content' => 'Konten baru yang sudah diperbarui.',
        'status' => 'published',
    ];

    $response = $this->actingAs($admin)
        ->put(route('admin.artikel.update', $article), $payload);

    $response->assertRedirect(route('admin.artikel.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'Judul Baru',
        'content' => 'Konten baru yang sudah diperbarui.',
        'status' => 'published',
    ]);
});

// Req 10.3: Admin dapat menghapus artikel.
it('menghapus artikel saat destroy', function () {
    $admin = articleCrudAdmin();

    $article = Article::factory()->create();

    $response = $this->actingAs($admin)
        ->delete(route('admin.artikel.destroy', $article));

    $response->assertRedirect(route('admin.artikel.index'));

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});

// Req 10.4: Field wajib (title, content) kosong menghasilkan error validasi dan tidak tersimpan.
it('menolak store dengan judul dan konten kosong dan tidak menyimpan artikel', function () {
    $admin = articleCrudAdmin();

    $response = $this->actingAs($admin)->post(route('admin.artikel.store'), [
        'title' => '',
        'content' => '',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['title', 'content']);

    $this->assertDatabaseCount('articles', 0);
});
