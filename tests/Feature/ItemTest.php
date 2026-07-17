<?php

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// makeItem() adalah helper LOKAL file ini — tidak konflik dengan Pest.php
function makeItem(array $attrs = []): Item
{
    return Item::factory()->create(array_merge([
        'name'     => 'Roti Tawar',
        'category' => 'Bakery_Jadi',
        'unit'     => 'pcs',
        'price'    => 15000,
        'stock'    => 10,
    ], $attrs));
}

// ─────────────────────────────────────────────
// Akses Daftar Produk (index)
// ─────────────────────────────────────────────

it('admin bisa melihat daftar semua produk', function () {
    $this->actingAs(adminUser())
        ->get(route('items.index'))
        ->assertOk()
        ->assertViewIs('items.index');
});

it('owner bisa melihat daftar semua produk', function () {
    $this->actingAs(ownerUser())
        ->get(route('items.index'))
        ->assertOk();
});

it('dapur hanya melihat produk kategori Bakery', function () {
    makeItem(['category' => 'ATK', 'name' => 'Bolpoin']);
    makeItem(['category' => 'Bakery_Jadi', 'name' => 'Croissant']);
    makeItem(['category' => 'Bakery_Bahan_Baku', 'name' => 'Tepung Terigu']);

    $response = $this->actingAs(dapurUser())
        ->get(route('items.index'));

    $response->assertOk();

    $items = $response->viewData('items');
    expect($items->pluck('name')->toArray())
        ->toContain('Croissant')
        ->toContain('Tepung Terigu')
        ->not->toContain('Bolpoin');
});

// ─────────────────────────────────────────────
// Tambah Produk (create / store) — hanya admin
// ─────────────────────────────────────────────

it('admin bisa membuka halaman tambah produk', function () {
    $this->actingAs(adminUser())
        ->get(route('items.create'))
        ->assertOk()
        ->assertViewIs('items.create');
});

it('non-admin dilarang membuka halaman tambah produk', function () {
    foreach ([ownerUser(), dapurUser()] as $user) {
        $this->actingAs($user)
            ->get(route('items.create'))
            ->assertForbidden();
    }
});

it('admin berhasil menambah produk baru', function () {
    $this->actingAs(adminUser())
        ->post(route('items.store'), [
            'name'        => 'Donat Coklat',
            'category'    => 'Bakery_Jadi',
            'unit'        => 'pcs',
            'price'       => 12000,
            'stock'       => 50,
            'description' => 'Donat premium',
        ])
        ->assertRedirect(route('items.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('items', ['name' => 'Donat Coklat']);
});

it('store produk gagal jika data wajib kosong', function () {
    $this->actingAs(adminUser())
        ->post(route('items.store'), [])
        ->assertSessionHasErrors(['name', 'category', 'unit', 'price', 'stock']);
});

it('store produk gagal jika kategori tidak valid', function () {
    $this->actingAs(adminUser())
        ->post(route('items.store'), [
            'name'     => 'Produk Baru',
            'category' => 'KategoriAsal',
            'unit'     => 'pcs',
            'price'    => 1000,
            'stock'    => 5,
        ])
        ->assertSessionHasErrors(['category']);
});

it('store produk gagal jika stok negatif', function () {
    $this->actingAs(adminUser())
        ->post(route('items.store'), [
            'name'     => 'Produk Baru',
            'category' => 'ATK',
            'unit'     => 'pcs',
            'price'    => 1000,
            'stock'    => -1,
        ])
        ->assertSessionHasErrors(['stock']);
});

it('produk baru memiliki SKU yang di-generate otomatis', function () {
    $this->actingAs(adminUser())
        ->post(route('items.store'), [
            'name'     => 'Tinta Printer',
            'category' => 'ATK',
            'unit'     => 'botol',
            'price'    => 25000,
            'stock'    => 3,
        ]);

    $item = Item::where('name', 'Tinta Printer')->first();
    expect($item->sku)->toStartWith('ATK-');
});

// ─────────────────────────────────────────────
// Edit / Update Produk — hanya admin
// ─────────────────────────────────────────────

it('admin bisa memperbarui data produk', function () {
    $item = makeItem(['name' => 'Roti Lama', 'price' => 8000]);

    $this->actingAs(adminUser())
        ->put(route('items.update', $item), [
            'name'     => 'Roti Baru',
            'category' => 'Bakery_Jadi',
            'unit'     => 'pcs',
            'price'    => 10000,
        ])
        ->assertRedirect(route('items.index'))
        ->assertSessionHas('success');

    expect($item->fresh()->name)->toBe('Roti Baru');
    expect((float) $item->fresh()->price)->toBe(10000.0);
});

it('non-admin dilarang update produk', function () {
    $item = makeItem();

    $this->actingAs(ownerUser())
        ->put(route('items.update', $item), [
            'name'     => 'Coba Edit',
            'category' => 'Bakery_Jadi',
            'unit'     => 'pcs',
            'price'    => 5000,
        ])
        ->assertForbidden();
});

// ─────────────────────────────────────────────
// Hapus Produk — soft delete, hanya admin
// ─────────────────────────────────────────────

it('admin bisa menghapus produk (soft delete)', function () {
    $item = makeItem(['name' => 'Produk Hapus']);

    $this->actingAs(adminUser())
        ->delete(route('items.destroy', $item))
        ->assertRedirect(route('items.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('items', ['id' => $item->id]);
});

it('non-admin dilarang menghapus produk', function () {
    $item = makeItem();

    $this->actingAs(dapurUser())
        ->delete(route('items.destroy', $item))
        ->assertForbidden();
});

// ─────────────────────────────────────────────
// Accessor & Helper Model
// ─────────────────────────────────────────────

it('is_critical true jika stok <= 5', function () {
    $item = makeItem(['stock' => 5]);
    expect($item->is_critical)->toBeTrue();

    $item->stock = 6;
    expect($item->is_critical)->toBeFalse();
});

it('category_label sesuai dengan nilai kategori', function () {
    $item = makeItem(['category' => 'Bakery_Jadi']);
    expect($item->category_label)->toBe('Cake & Pastry');

    $item->category = 'Bakery_Bahan_Baku';
    expect($item->category_label)->toBe('Bahan Baku');
});