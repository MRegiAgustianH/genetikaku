<?php

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Tests\RefreshDatabaseWithoutSeeding;

uses(TestTrait::class, RefreshDatabaseWithoutSeeding::class);

/*
 * Property 18 menguji kontrol akses area admin pada level rute/HTTP.
 * Untuk setiap rute di grup admin (middleware ['auth','admin']), permintaan dari
 * pengguna tanpa peran 'admin' — termasuk tamu (guest) — harus ditolak dan
 * dialihkan (redirect) ke rute 'login'. Sebagai kontras kewarasan, pengguna
 * dengan peran 'admin' tidak dialihkan ke login.
 *
 * Prefiks unik PROP18_ digunakan untuk konstanta/fungsi lingkup-berkas guna
 * menghindari tabrakan simbol global dengan berkas tes lain.
 */

// Daftar nama rute pada area admin yang diuji (di luar login/logout).
const PROP18_ADMIN_ROUTE_NAMES = [
    'admin.dashboard',
];

/**
 * Menghasilkan nilai peran non-admin yang acak (termasuk string arbitrer
 * yang bukan 'admin'), tetap menjaga agar tidak pernah sama dengan 'admin'.
 *
 * @return \Eris\Generator
 */
function prop18NonAdminRoleGenerator(): callable
{
    return Generator\map(
        function ($value): string {
            $role = is_string($value) ? trim($value) : (string) $value;

            // Pastikan tidak pernah menghasilkan 'admin' (atau kosong).
            if ($role === '' || strtolower($role) === 'admin') {
                $role = 'user';
            }

            return $role;
        },
        Generator\oneOf(
            Generator\constant('user'),
            Generator\constant('member'),
            Generator\constant('guest'),
            Generator\string(),
        ),
    );
}

// Feature: genetikaku-expert-system, Property 18: Route admin menolak akses non-admin
it('menolak akses pengguna non-admin ke rute area admin dan mengalihkan ke login', function () {
    $this->forAll(
        prop18NonAdminRoleGenerator(),
    )->then(function (string $nonAdminRole) {
        foreach (PROP18_ADMIN_ROUTE_NAMES as $routeName) {
            $user = User::factory()->create(['role' => $nonAdminRole]);

            $response = $this->actingAs($user)->get(route($routeName));

            $response->assertStatus(302);
            $response->assertRedirect(route('login'));
        }
    });
});

// Feature: genetikaku-expert-system, Property 18: Route admin menolak akses non-admin
it('menolak akses tamu (guest) ke rute area admin dan mengalihkan ke login', function () {
    foreach (PROP18_ADMIN_ROUTE_NAMES as $routeName) {
        $response = $this->get(route($routeName));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
});

// Feature: genetikaku-expert-system, Property 18: Route admin menolak akses non-admin
it('tidak mengalihkan pengguna admin ke login (kontras kewarasan)', function () {
    // Nonaktifkan Vite agar render halaman Inertia tidak bergantung pada aset
    // frontend yang sudah di-build; fokus uji ini hanya pada kontrol akses.
    $this->withoutVite();

    $admin = User::factory()->create(['role' => 'admin']);

    foreach (PROP18_ADMIN_ROUTE_NAMES as $routeName) {
        $response = $this->actingAs($admin)->get(route($routeName));

        $response->assertOk();
    }
});
