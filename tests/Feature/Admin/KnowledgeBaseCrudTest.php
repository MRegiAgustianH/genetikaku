<?php

use App\Models\KnowledgeBaseRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
 * Feature test CRUD Basis_Pengetahuan pada area admin (Req 12.1, 12.3, 12.4).
 *
 * Suite 'Feature' sudah menerapkan RefreshDatabaseWithoutSeeding secara global
 * melalui tests/Pest.php, sehingga tiap tes berjalan terisolasi tanpa seeding.
 */

/**
 * Buat pengguna dengan peran admin untuk mengakses grup route ['auth','admin'].
 */
function knowledgeBaseAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

// Req 12.1: Admin dapat membuat aturan Basis_Pengetahuan baru melalui store.
it('membuat aturan basis pengetahuan saat store dengan data valid', function () {
    $admin = knowledgeBaseAdmin();

    $payload = [
        'indicator' => 'Riwayat keluarga Thalassemia',
        'weight' => 3,
        'classification_mapping' => 'Carrier',
    ];

    $response = $this->actingAs($admin)
        ->post(route('admin.basis-pengetahuan.store'), $payload);

    $response->assertRedirect(route('admin.basis-pengetahuan.index'));
    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('knowledge_base_rules', [
        'indicator' => 'Riwayat keluarga Thalassemia',
        'weight' => 3,
        'classification_mapping' => 'Carrier',
    ]);
});

// Req 12.1: Admin dapat memperbarui aturan Basis_Pengetahuan.
it('memperbarui aturan basis pengetahuan saat update', function () {
    $admin = knowledgeBaseAdmin();

    $rule = KnowledgeBaseRule::factory()->create([
        'indicator' => 'Indikator Lama',
        'weight' => 1,
        'classification_mapping' => 'Normal',
    ]);

    $payload = [
        'indicator' => 'Indikator Baru',
        'weight' => 5,
        'classification_mapping' => 'Berisiko Tinggi',
    ];

    $response = $this->actingAs($admin)
        ->put(route('admin.basis-pengetahuan.update', $rule), $payload);

    $response->assertRedirect(route('admin.basis-pengetahuan.index'));
    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('knowledge_base_rules', [
        'id' => $rule->id,
        'indicator' => 'Indikator Baru',
        'weight' => 5,
        'classification_mapping' => 'Berisiko Tinggi',
    ]);
});

// Req 12.1: Admin dapat menghapus aturan Basis_Pengetahuan.
it('menghapus aturan basis pengetahuan saat destroy', function () {
    $admin = knowledgeBaseAdmin();

    $rule = KnowledgeBaseRule::factory()->create();

    $response = $this->actingAs($admin)
        ->delete(route('admin.basis-pengetahuan.destroy', $rule));

    $response->assertRedirect(route('admin.basis-pengetahuan.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('knowledge_base_rules', [
        'id' => $rule->id,
    ]);
});

// Req 12.4: Field wajib kosong menghasilkan error validasi dan tidak tersimpan.
it('menolak store dengan field wajib kosong dan tidak menyimpan aturan', function () {
    $admin = knowledgeBaseAdmin();

    $response = $this->actingAs($admin)->post(route('admin.basis-pengetahuan.store'), [
        'indicator' => '',
        'weight' => '',
        'classification_mapping' => '',
    ]);

    $response->assertSessionHasErrors(['indicator', 'weight', 'classification_mapping']);

    $this->assertDatabaseCount('knowledge_base_rules', 0);
});

// Req 12.4: classification_mapping di luar 3 kategori valid ditolak.
it('menolak store dengan pemetaan klasifikasi tidak valid', function () {
    $admin = knowledgeBaseAdmin();

    $response = $this->actingAs($admin)->post(route('admin.basis-pengetahuan.store'), [
        'indicator' => 'Riwayat anemia',
        'weight' => 2,
        'classification_mapping' => 'Kategori Tidak Dikenal',
    ]);

    $response->assertSessionHasErrors(['classification_mapping']);

    $this->assertDatabaseCount('knowledge_base_rules', 0);
});

// Req 12.3: Kegagalan teknis saat store memicu rollback transaksi + flash 'error',
// dan data tidak berubah (tidak ada baris baru tersimpan).
it('rollback dan flash error saat store gagal di dalam transaksi', function () {
    $admin = knowledgeBaseAdmin();

    // Paksa kegagalan teknis HANYA pada query tulis ke tabel target di dalam
    // transaksi controller, tanpa mengubah kode aplikasi. Listener dipicu pada
    // setiap query; kita batasi ke INSERT terhadap knowledge_base_rules agar
    // kegagalan terjadi tepat di dalam DB::transaction (bukan saat auth/route).
    DB::beforeExecuting(function (string $query) {
        if (str_contains($query, 'knowledge_base_rules')
            && str_starts_with(strtolower(ltrim($query)), 'insert')) {
            throw new RuntimeException('boom');
        }
    });

    $response = $this->actingAs($admin)->post(route('admin.basis-pengetahuan.store'), [
        'indicator' => 'Riwayat transfusi darah',
        'weight' => 4,
        'classification_mapping' => 'Normal',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Rollback berhasil: tidak ada baris yang tersisa.
    $this->assertDatabaseCount('knowledge_base_rules', 0);
});

// Req 12.3: Kegagalan teknis saat update memicu rollback + flash 'error',
// dan nilai sebelumnya dipertahankan utuh.
it('rollback dan flash error saat update gagal di dalam transaksi', function () {
    $admin = knowledgeBaseAdmin();

    $rule = KnowledgeBaseRule::factory()->create([
        'indicator' => 'Indikator Asli',
        'weight' => 2,
        'classification_mapping' => 'Normal',
    ]);

    DB::beforeExecuting(function (string $query) {
        if (str_contains($query, 'knowledge_base_rules')
            && str_starts_with(strtolower(ltrim($query)), 'update')) {
            throw new RuntimeException('boom');
        }
    });

    $response = $this->actingAs($admin)->put(route('admin.basis-pengetahuan.update', $rule), [
        'indicator' => 'Indikator Diubah',
        'weight' => 5,
        'classification_mapping' => 'Berisiko Tinggi',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Data sebelumnya dipertahankan utuh.
    $this->assertDatabaseHas('knowledge_base_rules', [
        'id' => $rule->id,
        'indicator' => 'Indikator Asli',
        'weight' => 2,
        'classification_mapping' => 'Normal',
    ]);
});
