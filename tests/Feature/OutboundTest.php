<?php

use App\Models\Item;
use App\Models\Outbound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
// Akses Index & Create
// ─────────────────────────────────────────────

it('semua role yang login bisa melihat daftar outbound', function () {
    foreach ([adminUser(), ownerUser(), dapurUser()] as $user) {
        $this->actingAs($user)
            ->get(route('outbounds.index'))
            ->assertOk();
    }
});

it('tamu diarahkan ke login saat akses outbound', function () {
    $this->get(route('outbounds.index'))
        ->assertRedirect(route('login'));
});

it('admin bisa membuka halaman catat barang keluar', function () {
    $this->actingAs(adminUser())
        ->get(route('outbounds.create'))
        ->assertOk()
        ->assertViewIs('outbounds.create');
});

it('dapur bisa membuka halaman catat barang keluar', function () {
    $this->actingAs(dapurUser())
        ->get(route('outbounds.create'))
        ->assertOk();
});

// ─────────────────────────────────────────────
// Store Outbound — sukses
// ─────────────────────────────────────────────

it('admin berhasil mencatat barang keluar manual dan stok berkurang', function () {
    $item = itemBakeryJadi(10);

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 4,
            'customer' => 'Pelanggan A',
            'date'     => now()->toDateString(),
            'notes'    => 'Pesanan reguler',
        ])
        ->assertRedirect(route('outbounds.index'))
        ->assertSessionHas('success');

    expect($item->fresh()->stock)->toBe(6);

    $this->assertDatabaseHas('outbounds', [
        'item_id'  => $item->id,
        'quantity' => 4,
        'source'   => 'manual',
        'customer' => 'Pelanggan A',
    ]);
});

it('dapur berhasil mencatat bahan baku keluar', function () {
    $item  = itemBahanBaku(30);
    $dapur = dapurUser();

    $this->actingAs($dapur)
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 5,
            'date'     => now()->toDateString(),
        ])
        ->assertRedirect(route('outbounds.index'));

    expect($item->fresh()->stock)->toBe(25);
});

it('source selalu "manual" untuk outbound via controller ini', function () {
    $item = itemBakeryJadi(10);

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 2,
            'date'     => now()->toDateString(),
        ]);

    $outbound = Outbound::first();
    expect($outbound->source)->toBe('manual');
});

// ─────────────────────────────────────────────
// Store Outbound — validasi gagal
// ─────────────────────────────────────────────

it('store outbound gagal jika field wajib kosong', function () {
    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [])
        ->assertSessionHasErrors(['item_id', 'quantity', 'date']);
});

it('store outbound gagal jika quantity 0 atau negatif', function () {
    $item = itemBakeryJadi();

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 0,
            'date'     => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['quantity']);
});

it('store outbound gagal jika quantity melebihi 10000', function () {
    $item = itemBakeryJadi(100);

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 10001,
            'date'     => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['quantity']);
});

it('store outbound gagal jika tanggal di masa depan', function () {
    $item = itemBakeryJadi();

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 1,
            'date'     => now()->addDay()->toDateString(),
        ])
        ->assertSessionHasErrors(['date']);
});

// ─────────────────────────────────────────────
// Cek Stok Tidak Mencukupi
// ─────────────────────────────────────────────

it('store outbound gagal jika stok tidak mencukupi', function () {
    $item = itemBakeryJadi(3);

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 10,
            'date'     => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['quantity']);

    expect($item->fresh()->stock)->toBe(3);
});

it('stok tidak berubah jika terjadi error (rollback transaksi)', function () {
    $item = itemBahanBaku(5);

    $this->actingAs(adminUser())
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 999,
            'date'     => now()->toDateString(),
        ]);

    expect($item->fresh()->stock)->toBe(5);
    $this->assertDatabaseMissing('outbounds', ['item_id' => $item->id]);
});

// ─────────────────────────────────────────────
// Pembatasan Role Dapur
// ─────────────────────────────────────────────

it('dapur tidak bisa mencatat keluar item ATK', function () {
    $item  = itemATK(5);
    $dapur = dapurUser();

    $this->actingAs($dapur)
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 1,
            'date'     => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['item_id']);

    expect($item->fresh()->stock)->toBe(5);
});

it('dapur tidak bisa mencatat keluar item Bakery_Jadi', function () {
    $item  = itemBakeryJadi(10);
    $dapur = dapurUser();

    $this->actingAs($dapur)
        ->post(route('outbounds.store'), [
            'item_id'  => $item->id,
            'quantity' => 2,
            'date'     => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['item_id']);
});

// ─────────────────────────────────────────────
// Accessor Model
// ─────────────────────────────────────────────

it('source_label mengembalikan "Manual" untuk outbound manual', function () {
    $outbound = Outbound::factory()->create(['source' => 'manual']);
    expect($outbound->source_label)->toBe('Manual');
});

it('source_label mengembalikan "Kasir / POS" untuk outbound dari kasir', function () {
    $outbound = Outbound::factory()->create(['source' => 'kasir']);
    expect($outbound->source_label)->toBe('Kasir / POS');
});