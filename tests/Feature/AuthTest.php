<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
// Halaman Login
// ─────────────────────────────────────────────

it('menampilkan halaman login untuk tamu', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertViewIs('auth.login');
});

it('redirect ke dashboard jika user yang sudah login mencoba buka /login', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('dashboard'));
});

// ─────────────────────────────────────────────
// Proses Login
// ─────────────────────────────────────────────

it('berhasil login dengan username dan password yang benar', function () {
    $user = User::factory()->create([
        'username' => 'adminrajawali',
        'password' => bcrypt('password123'),
        'role'     => 'admin',
    ]);

    $this->post(route('login.submit'), [
        'username' => 'adminrajawali',
        'password' => 'password123',
    ])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('gagal login jika password salah', function () {
    User::factory()->create([
        'username' => 'adminrajawali',
        'password' => bcrypt('password123'),
    ]);

    $this->post(route('login.submit'), [
        'username' => 'adminrajawali',
        'password' => 'passwordsalah',
    ])
        ->assertSessionHasErrors();

    $this->assertGuest();
});

it('gagal login jika username tidak ditemukan', function () {
    $this->post(route('login.submit'), [
        'username' => 'tidakada',
        'password' => 'apapun',
    ])
        ->assertSessionHasErrors();

    $this->assertGuest();
});

it('gagal login jika username atau password kosong', function () {
    $this->post(route('login.submit'), [])
        ->assertSessionHasErrors(['username', 'password']);
});

// ─────────────────────────────────────────────
// Logout
// ─────────────────────────────────────────────

it('berhasil logout dan diarahkan ke halaman login', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

// ─────────────────────────────────────────────
// Proteksi route — harus login dulu
// ─────────────────────────────────────────────

it('tamu diarahkan ke login jika mencoba akses dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});