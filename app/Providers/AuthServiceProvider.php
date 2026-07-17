<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // ── Gate: admin saja ─────────────────────────────────────────────
        // Dipakai: item CRUD, kasir/POS, laporan.export
        Gate::define('admin-only', fn($user) => $user->role === 'admin');

        // ── Gate: lihat laporan → admin + owner ─────────────────────────
        // Dipakai: route /laporan (index, export Excel, export PDF)
        Gate::define('view-laporan', fn($user) => in_array($user->role, ['admin', 'owner']));

        // ── Gate: aksi level owner ───────────────────────────────────────
        // Alias dari view-laporan; tersedia jika butuh pemisahan di masa depan
        Gate::define('owner-or-admin', fn($user) => in_array($user->role, ['admin', 'owner']));

        // ── Gate: akses fitur dapur ──────────────────────────────────────
        // Contoh pemakaian di Blade: @can('dapur-access') (belum dipakai di route)
        Gate::define('dapur-access', fn($user) => in_array($user->role, ['admin', 'dapur']));

        // ── Gate: catat inbound/outbound ─────────────────────────────────
        // Nama 'admin-or-dapur' dipertahankan agar route middleware tidak perlu diubah.
        // PENTING: owner juga dicakup sehingga owner bisa input inbound/outbound
        // melalui form, meskipun shortcut di dashboard-nya disembunyikan.
        Gate::define('admin-or-dapur', fn($user) => in_array($user->role, ['admin', 'dapur', 'owner']));

        // ── Gate: alias deskriptif untuk gate di atas ───────────────────
        Gate::define('can-record-stock', fn($user) => in_array($user->role, ['admin', 'dapur', 'owner']));

        // ── Gate: lihat katalog produk ───────────────────────────────────
        // Semua role authenticated bisa lihat item; pembatasan per kategori
        // dihandle di ItemController@show dan @index (bukan di gate ini).
        Gate::define('view-katalog', fn($user) => in_array($user->role, ['admin', 'owner', 'dapur']));
    }
}