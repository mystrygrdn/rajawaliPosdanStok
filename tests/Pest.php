<?php

/*
|--------------------------------------------------------------------------
| tests/Pest.php  ← FILE INI HARUS ADA DI DALAM FOLDER tests/
|--------------------------------------------------------------------------
| Pest v3 secara otomatis me-load tests/Pest.php.
| uses() di sini memakai __DIR__ agar path selalu tepat
| relatif terhadap lokasi file ini (tests/).
*/

uses(Tests\TestCase::class)->in(__DIR__ . '/Feature');
uses(Tests\TestCase::class)->in(__DIR__ . '/Unit');

// ─────────────────────────────────────────────
// Helper: User
// ─────────────────────────────────────────────

function adminUser(): \App\Models\User
{
    return \App\Models\User::factory()->create(['role' => 'admin']);
}

function ownerUser(): \App\Models\User
{
    return \App\Models\User::factory()->create(['role' => 'owner']);
}

function dapurUser(): \App\Models\User
{
    return \App\Models\User::factory()->create(['role' => 'dapur']);
}

// ─────────────────────────────────────────────
// Helper: Item
// ─────────────────────────────────────────────

function itemBakeryJadi(int $stock = 10): \App\Models\Item
{
    return \App\Models\Item::factory()->create([
        'category' => 'Bakery_Jadi',
        'stock'    => $stock,
        'unit'     => 'pcs',
        'price'    => 15000,
    ]);
}

function itemBahanBaku(int $stock = 20): \App\Models\Item
{
    return \App\Models\Item::factory()->create([
        'category' => 'Bakery_Bahan_Baku',
        'stock'    => $stock,
        'unit'     => 'kg',
        'price'    => 5000,
    ]);
}

function itemATK(int $stock = 5): \App\Models\Item
{
    return \App\Models\Item::factory()->create([
        'category' => 'ATK',
        'stock'    => $stock,
        'unit'     => 'pcs',
        'price'    => 10000,
    ]);
}

function itemJual(int $stock = 20, float $price = 15000): \App\Models\Item
{
    return \App\Models\Item::factory()->create([
        'category' => 'Bakery_Jadi',
        'stock'    => $stock,
        'price'    => $price,
        'unit'     => 'pcs',
    ]);
}