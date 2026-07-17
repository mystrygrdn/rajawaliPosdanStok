<?php

use App\Models\Inbound;
use App\Models\Item;
use App\Models\Outbound;
use App\Models\SaleTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
// Helper lokal
// ─────────────────────────────────────────────

/**
 * Buat pasangan item + inbound hari ini (stok awal via inbound).
 * Karena laporan menghitung stok dari riwayat inbound/outbound,
 * item harus punya inbound agar muncul di stockSummary.
 */
function itemDenganStok(string $category = 'Bakery_Jadi', int $qty = 10, float $price = 15000): Item
{
    $item = Item::factory()->create([
        'category' => $category,
        'stock'    => $qty,
        'price'    => $price,
        'unit'     => 'pcs',
    ]);

    Inbound::factory()->create([
        'item_id'  => $item->id,
        'quantity' => $qty,
        'date'     => now()->toDateString(),
    ]);

    return $item;
}

function saleTrx(User $user, Item $item, int $qty = 2, int $total = 30000): SaleTransaction
{
    $sale = SaleTransaction::factory()->create([
        'user_id'        => $user->id,
        'total_amount'   => $total,
        'paid_amount'    => $total,
        'change_amount'  => 0,
        'payment_method' => 'cash',
        'date'           => now()->toDateString(),
    ]);

    Outbound::factory()->create([
        'item_id'             => $item->id,
        'user_id'             => $user->id,
        'sale_transaction_id' => $sale->id,
        'quantity'            => $qty,
        'source'              => 'kasir',
        'date'                => now()->toDateString(),
    ]);

    return $sale;
}

// ─────────────────────────────────────────────
// Akses & Gate
// ─────────────────────────────────────────────

it('admin bisa membuka halaman laporan', function () {
    $this->actingAs(adminUser())
        ->get(route('laporan.index'))
        ->assertOk()
        ->assertViewIs('laporan.index');
});

it('owner bisa membuka halaman laporan', function () {
    $this->actingAs(ownerUser())
        ->get(route('laporan.index'))
        ->assertOk();
});

it('dapur dilarang membuka halaman laporan', function () {
    $this->actingAs(dapurUser())
        ->get(route('laporan.index'))
        ->assertForbidden();
});

it('tamu diarahkan ke login', function () {
    $this->get(route('laporan.index'))
        ->assertRedirect(route('login'));
});

// ─────────────────────────────────────────────
// View Data — index
// ─────────────────────────────────────────────

it('halaman laporan mengirim variabel wajib ke view', function () {
    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index'));

    $response->assertViewHasAll([
        'periodType',
        'periodLabel',
        'startDate',
        'endDate',
        'inbounds',
        'manualOutbounds',
        'totalInboundQty',
        'totalOutboundQty',
        'totalOutboundValue',
        'stockSummary',
        'salesTransactions',
        'salesCount',
        'salesTotal',
        'operators',
        'categories',
    ]);
});

it('default period_type adalah bulanan', function () {
    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index'));

    expect($response->viewData('periodType'))->toBe('bulanan');
});

it('period_type harian menghasilkan startDate == endDate', function () {
    $today = now()->toDateString();

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => $today,
        ]));

    expect($response->viewData('startDate'))->toBe($today);
    expect($response->viewData('endDate'))->toBe($today);
    expect($response->viewData('periodType'))->toBe('harian');
});

it('period_type bulanan menghasilkan range awal–akhir bulan', function () {
    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type'  => 'bulanan',
            'period_month' => now()->format('Y-m'),
        ]));

    $start = $response->viewData('startDate');
    $end   = $response->viewData('endDate');

    expect($start)->toBe(now()->startOfMonth()->toDateString());
    expect($end)->toBe(now()->endOfMonth()->toDateString());
});

it('period_type tahunan menghasilkan range 1 Jan – 31 Des', function () {
    $year = now()->year;

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'tahunan',
            'period_year' => $year,
        ]));

    expect($response->viewData('startDate'))->toBe("{$year}-01-01");
    expect($response->viewData('endDate'))->toBe("{$year}-12-31");
});

it('period_type custom menggunakan start_date dan end_date dari request', function () {
    $start = now()->subDays(7)->toDateString();
    $end   = now()->toDateString();

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'custom',
            'start_date'  => $start,
            'end_date'    => $end,
        ]));

    expect($response->viewData('startDate'))->toBe($start);
    expect($response->viewData('endDate'))->toBe($end);
});

// ─────────────────────────────────────────────
// Data Inbound & Outbound dalam periode
// ─────────────────────────────────────────────

it('totalInboundQty menghitung benar hanya dalam periode', function () {
    $item = Item::factory()->create(['stock' => 0, 'category' => 'Bakery_Jadi']);

    // 2 inbound hari ini
    Inbound::factory()->create(['item_id' => $item->id, 'quantity' => 5, 'date' => now()->toDateString()]);
    Inbound::factory()->create(['item_id' => $item->id, 'quantity' => 3, 'date' => now()->toDateString()]);

    // 1 inbound bulan lalu — tidak boleh terhitung jika filter harian
    Inbound::factory()->create(['item_id' => $item->id, 'quantity' => 99, 'date' => now()->subMonth()->toDateString()]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    expect($response->viewData('totalInboundQty'))->toBe(8);
});

it('totalOutboundQty menghitung semua outbound (kasir + manual) dalam periode', function () {
    $item  = itemDenganStok();
    $admin = adminUser();

    // 1 outbound manual
    Outbound::factory()->create([
        'item_id'  => $item->id,
        'user_id'  => $admin->id,
        'quantity' => 3,
        'source'   => 'manual',
        'date'     => now()->toDateString(),
    ]);

    // 1 outbound kasir
    Outbound::factory()->create([
        'item_id'  => $item->id,
        'user_id'  => $admin->id,
        'quantity' => 4,
        'source'   => 'kasir',
        'date'     => now()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    expect($response->viewData('totalOutboundQty'))->toBe(7);
});

it('manualOutbounds tidak menyertakan outbound dari kasir', function () {
    $item  = itemDenganStok();
    $admin = adminUser();

    Outbound::factory()->create([
        'item_id'  => $item->id, 'user_id' => $admin->id,
        'quantity' => 2, 'source' => 'manual', 'date' => now()->toDateString(),
    ]);
    Outbound::factory()->create([
        'item_id'  => $item->id, 'user_id' => $admin->id,
        'quantity' => 5, 'source' => 'kasir',  'date' => now()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    $manual = $response->viewData('manualOutbounds');

    expect($manual->count())->toBe(1);
    expect($manual->first()->source)->toBe('manual');
});

it('salesTotal menjumlahkan total_amount semua SaleTransaction dalam periode', function () {
    $item  = itemDenganStok('Bakery_Jadi', 50);
    $admin = adminUser();

    saleTrx($admin, $item, 2, 30000);
    saleTrx($admin, $item, 1, 15000);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    expect((int) $response->viewData('salesTotal'))->toBe(45000);
    expect($response->viewData('salesCount'))->toBe(2);
});

it('salesTransactions tidak menyertakan transaksi di luar periode', function () {
    $item  = itemDenganStok();
    $admin = adminUser();

    // Transaksi hari ini
    saleTrx($admin, $item, 1, 10000);

    // Transaksi kemarin — tidak boleh muncul di filter harian hari ini
    $saleKemarin = SaleTransaction::factory()->create([
        'user_id'        => $admin->id,
        'total_amount'   => 99999,
        'paid_amount'    => 99999,
        'change_amount'  => 0,
        'payment_method' => 'cash',
        'date'           => now()->subDay()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    $ids = $response->viewData('salesTransactions')->pluck('id');

    expect($ids)->not->toContain($saleKemarin->id);
    expect((int) $response->viewData('salesTotal'))->toBe(10000);
});

// ─────────────────────────────────────────────
// Filter
// ─────────────────────────────────────────────

it('filter_category membatasi inbound hanya kategori yang dipilih', function () {
    $bakery = itemDenganStok('Bakery_Jadi',       5);
    $atk    = itemDenganStok('ATK',               5);

    // Tambah inbound untuk ATK di hari yang sama
    Inbound::factory()->create([
        'item_id'  => $atk->id,
        'quantity' => 7,
        'date'     => now()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type'     => 'harian',
            'period_date'     => now()->toDateString(),
            'filter_category' => 'Bakery_Jadi',
        ]));

    $inbounds = $response->viewData('inbounds');

    // Semua inbound yang muncul harus kategori Bakery_Jadi
    expect($inbounds->every(fn($ib) => $ib->item->category === 'Bakery_Jadi'))->toBeTrue();
    // ATK tidak boleh muncul
    expect($inbounds->pluck('item_id'))->not->toContain($atk->id);
});

it('filter_operator membatasi data ke user yang dipilih', function () {
    $admin = adminUser();
    $owner = ownerUser();
    $item  = itemDenganStok();

    Inbound::factory()->create([
        'item_id'  => $item->id,
        'user_id'  => $admin->id,
        'quantity' => 3,
        'date'     => now()->toDateString(),
    ]);
    Inbound::factory()->create([
        'item_id'  => $item->id,
        'user_id'  => $owner->id,
        'quantity' => 9,
        'date'     => now()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type'     => 'harian',
            'period_date'     => now()->toDateString(),
            'filter_operator' => $admin->id,
        ]));

    $inbounds = $response->viewData('inbounds');

    expect($inbounds->every(fn($ib) => $ib->user_id === $admin->id))->toBeTrue();
    expect($response->viewData('totalInboundQty'))->toBe(3);
});

// ─────────────────────────────────────────────
// Stock Summary
// ─────────────────────────────────────────────

it('stockSummary berisi data per kategori', function () {
    itemDenganStok('Bakery_Jadi',       10);
    itemDenganStok('Bakery_Bahan_Baku', 20);

    $response = $this->actingAs(ownerUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    $summary  = $response->viewData('stockSummary');
    $cats     = $summary->pluck('category')->toArray();

    expect($cats)->toContain('Bakery_Jadi');
    expect($cats)->toContain('Bakery_Bahan_Baku');
});

it('stockSummary menghitung stok_akhir dengan benar (masuk - keluar)', function () {
    $item  = itemDenganStok('Bakery_Jadi', 10); // inbound 10 hari ini
    $admin = adminUser();

    // Keluar 3
    Outbound::factory()->create([
        'item_id'  => $item->id,
        'user_id'  => $admin->id,
        'quantity' => 3,
        'source'   => 'manual',
        'date'     => now()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->get(route('laporan.index', [
            'period_type' => 'harian',
            'period_date' => now()->toDateString(),
        ]));

    $summary = $response->viewData('stockSummary');
    $bakery  = $summary->firstWhere('category', 'Bakery_Jadi');

    expect($bakery)->not->toBeNull();
    expect($bakery->mutasi_masuk)->toBe(10);
    expect($bakery->mutasi_keluar)->toBe(3);
    expect($bakery->stok_akhir)->toBe(7);
});

// ─────────────────────────────────────────────
// AJAX: stockByDay
// ─────────────────────────────────────────────

it('stockByDay mengembalikan JSON dengan struktur yang benar', function () {
    itemDenganStok('Bakery_Jadi', 5);

    $response = $this->actingAs(adminUser())
        ->getJson(route('laporan.stock-by-day', [
            'date'     => now()->toDateString(),
            'category' => 'Bakery_Jadi',
        ]));

    $response->assertOk()
        ->assertJsonStructure([
            'date',
            'date_raw',
            'category',
            'category_label',
            'items',
            'totals' => [
                'stok_awal',
                'mutasi_masuk',
                'mutasi_keluar',
                'stok_akhir',
                'ending_value',
            ],
        ]);
});

it('stockByDay mengembalikan items kosong jika tidak ada aktivitas', function () {
    $response = $this->actingAs(adminUser())
        ->getJson(route('laporan.stock-by-day', [
            'date' => now()->toDateString(),
        ]));

    $response->assertOk();
    expect($response->json('items'))->toBeEmpty();
    expect($response->json('totals.stok_akhir'))->toBe(0);
});

it('stockByDay menghitung mutasi_masuk dan mutasi_keluar hari itu saja', function () {
    $item  = Item::factory()->create(['category' => 'ATK', 'stock' => 20, 'price' => 5000, 'unit' => 'pcs']);
    $admin = adminUser();

    // Inbound kemarin (stok awal)
    Inbound::factory()->create([
        'item_id'  => $item->id,
        'quantity' => 20,
        'date'     => now()->subDay()->toDateString(),
    ]);

    // Inbound hari ini
    Inbound::factory()->create([
        'item_id'  => $item->id,
        'quantity' => 5,
        'date'     => now()->toDateString(),
    ]);

    // Outbound hari ini
    Outbound::factory()->create([
        'item_id'  => $item->id,
        'user_id'  => $admin->id,
        'quantity' => 3,
        'source'   => 'manual',
        'date'     => now()->toDateString(),
    ]);

    $response = $this->actingAs(adminUser())
        ->getJson(route('laporan.stock-by-day', [
            'date'     => now()->toDateString(),
            'category' => 'ATK',
        ]));

    $response->assertOk();
    expect($response->json('totals.stok_awal'))->toBe(20);    // dari kemarin
    expect($response->json('totals.mutasi_masuk'))->toBe(5);  // hanya hari ini
    expect($response->json('totals.mutasi_keluar'))->toBe(3); // hanya hari ini
    expect($response->json('totals.stok_akhir'))->toBe(22);   // 20+5-3
});

it('stockByDay bisa filter per kategori', function () {
    $bakery = Item::factory()->create(['category' => 'Bakery_Jadi',       'stock' => 0, 'unit' => 'pcs']);
    $atk    = Item::factory()->create(['category' => 'ATK',               'stock' => 0, 'unit' => 'pcs']);

    Inbound::factory()->create(['item_id' => $bakery->id, 'quantity' => 8,  'date' => now()->toDateString()]);
    Inbound::factory()->create(['item_id' => $atk->id,   'quantity' => 12, 'date' => now()->toDateString()]);

    $response = $this->actingAs(adminUser())
        ->getJson(route('laporan.stock-by-day', [
            'date'     => now()->toDateString(),
            'category' => 'ATK',
        ]));

    $items = collect($response->json('items'));

    // Hanya ATK yang muncul
    expect($items)->not->toBeEmpty();
    expect($response->json('totals.mutasi_masuk'))->toBe(12);
});

it('stockByDay hanya bisa diakses admin dan owner', function () {
    $this->actingAs(dapurUser())
        ->getJson(route('laporan.stock-by-day'))
        ->assertForbidden();
});

it('stockByDay mengembalikan category_label yang benar', function () {
    itemDenganStok('Bakery_Jadi', 5);

    $response = $this->actingAs(adminUser())
        ->getJson(route('laporan.stock-by-day', [
            'date'     => now()->toDateString(),
            'category' => 'Bakery_Jadi',
        ]));

    expect($response->json('category_label'))->toBe('Cake & Pastry');
});

// ─────────────────────────────────────────────
// Export (akses & response type)
// ─────────────────────────────────────────────

it('admin bisa mengakses export Excel dan mendapat file xlsx', function () {
    $this->actingAs(adminUser())
        ->get(route('laporan.export.excel'))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('admin bisa mengakses export PDF dan mendapat file pdf', function () {
    $this->actingAs(adminUser())
        ->get(route('laporan.export.pdf'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('dapur tidak bisa export Excel', function () {
    $this->actingAs(dapurUser())
        ->get(route('laporan.export.excel'))
        ->assertForbidden();
});

it('dapur tidak bisa export PDF', function () {
    $this->actingAs(dapurUser())
        ->get(route('laporan.export.pdf'))
        ->assertForbidden();
});