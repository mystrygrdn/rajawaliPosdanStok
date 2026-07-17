<?php

use App\Models\Item;
use App\Models\SaleTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// checkoutPayload() adalah helper LOKAL file ini — tidak konflik dengan Pest.php
// karena Pest.php tidak mendefinisikan fungsi bernama checkoutPayload
function checkoutPayload(Item $item, array $overrides = []): array
{
    return array_merge([
        'cart'            => [['id' => $item->id, 'quantity' => 2]],
        'total_amount'    => 30000,
        'received_amount' => 50000,
        'payment_method'  => 'cash',
    ], $overrides);
}

// ─────────────────────────────────────────────
// Akses Halaman Kasir
// ─────────────────────────────────────────────

it('admin bisa membuka halaman kasir', function () {
    $this->actingAs(adminUser())
        ->get(route('kasir.index'))
        ->assertOk()
        ->assertViewIs('cashier.index');
});

it('owner dilarang membuka halaman kasir', function () {
    $this->actingAs(ownerUser())
        ->get(route('kasir.index'))
        ->assertForbidden();
});

it('dapur dilarang membuka halaman kasir', function () {
    $this->actingAs(dapurUser())
        ->get(route('kasir.index'))
        ->assertForbidden();
});

it('tamu diarahkan ke login', function () {
    $this->get(route('kasir.index'))
        ->assertRedirect(route('login'));
});

it('halaman kasir tidak menampilkan item Bakery_Bahan_Baku', function () {
    Item::factory()->create(['category' => 'Bakery_Bahan_Baku', 'stock' => 5]);
    $jadi = Item::factory()->create(['category' => 'Bakery_Jadi', 'stock' => 5]);

    $response = $this->actingAs(adminUser())->get(route('kasir.index'));
    $items = $response->viewData('items');

    expect($items->pluck('id')->toArray())
        ->toContain($jadi->id)
        ->not->toContain(Item::where('category', 'Bakery_Bahan_Baku')->first()->id);
});

it('halaman kasir tidak menampilkan item stok 0', function () {
    Item::factory()->create(['category' => 'Bakery_Jadi', 'stock' => 0, 'name' => 'Habis']);
    $ada = Item::factory()->create(['category' => 'Bakery_Jadi', 'stock' => 5, 'name' => 'Ada']);

    $items = $this->actingAs(adminUser())
        ->get(route('kasir.index'))
        ->viewData('items');

    expect($items->pluck('name')->toArray())
        ->toContain('Ada')
        ->not->toContain('Habis');
});

// ─────────────────────────────────────────────
// Checkout — sukses
// ─────────────────────────────────────────────

it('checkout berhasil membuat SaleTransaction dan Outbound', function () {
    $item = itemJual(20, 15000);

    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), checkoutPayload($item))
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('sale_transactions', ['total_amount' => 30000]);
    $this->assertDatabaseHas('outbounds', ['item_id' => $item->id, 'quantity' => 2, 'source' => 'kasir']);
});

it('checkout mengurangi stok item dengan benar', function () {
    $item = itemJual(20);

    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), checkoutPayload($item, ['cart' => [['id' => $item->id, 'quantity' => 3]]]));

    expect($item->fresh()->stock)->toBe(17);
});

it('kembalian dihitung dengan benar (received - total)', function () {
    $item = itemJual(10, 10000);

    $response = $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), [
            'cart'            => [['id' => $item->id, 'quantity' => 1]],
            'total_amount'    => 10000,
            'received_amount' => 50000,
            'payment_method'  => 'cash',
        ])
        ->assertOk();

    expect($response->json('change_amount'))->toEqual(40000);
});

it('kembalian tidak negatif jika received == total', function () {
    $item = itemJual(10, 10000);

    $response = $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), [
            'cart'            => [['id' => $item->id, 'quantity' => 1]],
            'total_amount'    => 10000,
            'received_amount' => 10000,
            'payment_method'  => 'qris',
        ]);

    expect($response->json('change_amount'))->toEqual(0);
});

it('checkout menyimpan metode pembayaran dengan benar', function () {
    $item = itemJual();

    foreach (['cash', 'qris', 'transfer'] as $method) {
        $this->actingAs(adminUser())
            ->postJson(route('kasir.checkout'), checkoutPayload($item, ['payment_method' => $method]));
    }

    foreach (['cash', 'qris', 'transfer'] as $method) {
        $this->assertDatabaseHas('sale_transactions', ['payment_method' => $method]);
    }
});

it('checkout bisa memproses beberapa item sekaligus', function () {
    $item1 = itemJual(10, 10000);
    $item2 = itemJual(5, 20000);

    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), [
            'cart' => [
                ['id' => $item1->id, 'quantity' => 2],
                ['id' => $item2->id, 'quantity' => 1],
            ],
            'total_amount'    => 40000,
            'received_amount' => 50000,
            'payment_method'  => 'cash',
        ])
        ->assertJson(['success' => true]);

    expect($item1->fresh()->stock)->toBe(8);
    expect($item2->fresh()->stock)->toBe(4);
});

// ─────────────────────────────────────────────
// Checkout — validasi & error
// ─────────────────────────────────────────────

it('checkout gagal jika cart kosong', function () {
    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), [
            'cart'            => [],
            'total_amount'    => 0,
            'received_amount' => 0,
            'payment_method'  => 'cash',
        ])
        ->assertStatus(422);
});

it('checkout gagal jika item tidak ditemukan di DB', function () {
    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), [
            'cart'            => [['id' => 9999, 'quantity' => 1]],
            'total_amount'    => 10000,
            'received_amount' => 10000,
            'payment_method'  => 'cash',
        ])
        ->assertStatus(422);
});

it('checkout gagal jika metode pembayaran tidak valid', function () {
    $item = itemJual();

    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), checkoutPayload($item, ['payment_method' => 'bitcoin']))
        ->assertStatus(422);
});

it('checkout gagal jika stok tidak mencukupi', function () {
    $item = itemJual(2);

    $this->actingAs(adminUser())
        ->postJson(route('kasir.checkout'), [
            'cart'            => [['id' => $item->id, 'quantity' => 10]],
            'total_amount'    => 150000,
            'received_amount' => 200000,
            'payment_method'  => 'cash',
        ])
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($item->fresh()->stock)->toBe(2);
    $this->assertDatabaseMissing('sale_transactions', ['total_amount' => 150000]);
});

it('owner tidak bisa melakukan checkout', function () {
    $item = itemJual();

    $this->actingAs(ownerUser())
        ->postJson(route('kasir.checkout'), checkoutPayload($item))
        ->assertForbidden();
});

// ─────────────────────────────────────────────
// Nota / Struk
// ─────────────────────────────────────────────

it('admin bisa mengambil data nota transaksi', function () {
    $item  = itemJual(10, 15000);
    $admin = adminUser();

    $response = $this->actingAs($admin)
        ->postJson(route('kasir.checkout'), checkoutPayload($item));

    $transactionId = $response->json('transaction_id');

    $nota = $this->actingAs($admin)
        ->getJson(route('kasir.nota', $transactionId))
        ->assertOk();

    expect($nota->json('id'))->toBe($transactionId);
    expect($nota->json('items'))->not->toBeEmpty();
    expect($nota->json('daily_no'))->toBe(1);
});

it('nota mengembalikan daily_no yang incremental dalam satu hari', function () {
    $item  = itemJual(50, 10000);
    $admin = adminUser();

    $id1 = $this->actingAs($admin)
        ->postJson(route('kasir.checkout'), checkoutPayload($item))->json('transaction_id');

    $id2 = $this->actingAs($admin)
        ->postJson(route('kasir.checkout'), checkoutPayload($item))->json('transaction_id');

    $no1 = $this->actingAs($admin)->getJson(route('kasir.nota', $id1))->json('daily_no');
    $no2 = $this->actingAs($admin)->getJson(route('kasir.nota', $id2))->json('daily_no');

    expect($no2)->toBe($no1 + 1);
});