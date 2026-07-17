<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
               // ── User: Admin (Kasir) ──────────────────────────────────────
        User::updateOrCreate(
            ['username' => 'admin01'],
            [
                'name'     => 'Liska',
                'password' => Hash::make('password1234'),
                'role'     => 'admin',
                'must_change_password' => true, // Wajib diset true agar user dipaksa mengganti password dev
            ]
        );

        // ── User: Owner ──────────────────────────────────────────────
        User::updateOrCreate(
            ['username' => 'owner01'],
            [
                'name'     => 'Missie',
                'password' => Hash::make('password0987'),
                'role'     => 'owner',
                'must_change_password' => true,
            ]
        );

        // ── User: Dapur ────────────────────────────────────────────
        User::updateOrCreate(
            ['username' => 'dapur01'],
            [
                'name'     => 'Bagian Dapur',
                'password' => Hash::make('password123'),
                'role'     => 'dapur',
                'must_change_password' => true,
            ]
        );
        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: ATK (Alat Tulis Kantor)
        // ═══════════════════════════════════════════════════════════════
        $atkItems = [
            [
                'sku'         => 'ATK-001',
                'name'        => 'Kertas HVS Sidu A4 (RIM)',
                'category'    => 'ATK',
                'unit'        => 'rim',
                'price'       => 54000,
            ],
            [
                'sku'         => 'ATK-002',
                'name'        => 'Kertas HVS Sidu A4 (DUS)',
                'category'    => 'ATK',
                'unit'        => 'dus',
                'stock'       => 0,
                'price'       => 260000,
            ],
            [
                'sku'         => 'ATK-003',
                'name'        => 'Kertas HVS Sidu F4 (RIM)',
                'category'    => 'ATK',
                'unit'        => 'rim',
                'stock'       => 0,
                'price'       => 59000,
            ],
            [
                'sku'         => 'ATK-004',
                'name'        => 'Kertas HVS Sidu F4 (DUS)',
                'category'    => 'ATK',
                'unit'        => 'dus',
                'stock'       => 0,
                'price'       => 285000,
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: Elektronik (Tinta & Cartridge)
        // ═══════════════════════════════════════════════════════════════
        $elektronikItems = [
            [
                'sku'         => 'ELK-001',
                'name'        => 'Tinta Epson Black L-Series',
                'category'    => 'Elektronik',
                'unit'        => 'botol',
                'stock'       => 0,
                'price'       => 85000,
            ],
            [
                'sku'         => 'ELK-002',
                'name'        => 'Tinta Epson Color Set CMY',
                'category'    => 'Elektronik',
                'unit'        => 'set',
                'stock'       => 0,
                'price'       => 240000,
            ],
            [
                'sku'         => 'ELK-003',
                'name'        => 'Cartridge HP 680 Black',
                'category'    => 'Elektronik',
                'unit'        => 'pcs',
                'stock'       => 0,  // Stok kritis!
                'price'       => 195000,
            ],
            [
                'sku'         => 'ELK-004',
                'name'        => 'Tinta Canon GI-790 Yellow',
                'category'    => 'Elektronik',
                'unit'        => 'botol',
                'stock'       => 0,
                'price'       => 95000,
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: Bakery — Produk Jadi
        // ═══════════════════════════════════════════════════════════════
        $bakeryJadiItems = [
            [
                'sku'         => 'BKR-001',
                'name'        => 'Pie Buah',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'pcs',
                'stock'       => 0,
                'price'       => 3000,
            ],
            [
                'sku'         => 'BKR-002',
                'name'        => 'Susen',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'pcs',
                'stock'       => 0,
                'price'       => 2500,
            ],
            [
                'sku'         => 'BKR-003',
                'name'        => 'Cake Marmer',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'loyang',
                'stock'       => 0,
                'price'       => 75000,
            ],
            [
                'sku'         => 'BKR-004',
                'name'        => 'Cake Sunkist',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'loyang',
                'stock'       => 0,
                'price'       => 80000,
            ],
            [
                'sku'         => 'BKR-005',
                'name'        => 'Cake Ombekuk',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'loyang',
                'stock'       => 0,
                'price'       => 80000,
            ],
            [
                'sku'         => 'BKR-006',
                'name'        => 'Brudel',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'loyang',
                'stock'       => 0,
                'price'       => 65000,
            ],
            [
                'sku'         => 'BKR-007',
                'name'        => 'Pia Keju',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'pcs',
                'stock'       => 0,
                'price'       => 4000,
            ],
            [
                'sku'         => 'BKR-008',
                'name'        => 'Pia Coklat Keju',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'pcs',
                'stock'       => 0,
                'price'       => 4000,
            ],
            [
                'sku'         => 'BKR-009',
                'name'        => 'Terompet',
                'category'    => 'Bakery_Jadi',
                'unit'        => 'pcs',
                'stock'       => 0,
                'price'       => 2500,
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: Bakery — Bahan Baku
        // ═══════════════════════════════════════════════════════════════
        $bakeryBahanBakuItems = [
            [
                'sku'         => 'BHN-001',
                'name'        => 'Tepung Terigu Cakra Kembar 1kg',
                'category'    => 'Bakery_Bahan_Baku',
                'unit'        => 'kg',
                'stock'       => 0,
                'price'       => 16000,
            ],
            [
                'sku'         => 'BHN-002',
                'name'        => 'Mentega Blue Band 200gr',
                'category'    => 'Bakery_Bahan_Baku',
                'unit'        => 'pcs',
                'stock'       => 0,
                'price'       => 22000,
            ],
            [
                'sku'         => 'BHN-003',
                'name'        => 'Gula Pasir Lokal 1kg',
                'category'    => 'Bakery_Bahan_Baku',
                'unit'        => 'kg',
                'stock'       => 0,
                'price'       => 18000,
            ],
            [
                'sku'         => 'BHN-004',
                'name'        => 'Telur Ayam Negeri',
                'category'    => 'Bakery_Bahan_Baku',
                'unit'        => 'butir',
                'stock'       => 0,
                'price'       => 2500,
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: Minuman
        // ═══════════════════════════════════════════════════════════════
        $minumanItems = [
            [
                'sku'      => 'MNM-001',
                'name'     => 'Air Mineral 600ml',
                'category' => 'Minuman',
                'unit'     => 'botol',
                'stock'    => 0,
                'price'    => 4000,
            ],
            [
                'sku'      => 'MNM-002',
                'name'     => 'Teh Kotak',
                'category' => 'Minuman',
                'unit'     => 'kotak',
                'stock'    => 0,
                'price'    => 5000,
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: Snack
        // ═══════════════════════════════════════════════════════════════
        $snackItems = [
            [
                'sku'      => 'SNK-001',
                'name'     => 'Keripik Pisang',
                'category' => 'Snack',
                'unit'     => 'pcs',
                'stock'    => 0,
                'price'    => 5000,
            ],
            [
                'sku'      => 'SNK-002',
                'name'     => 'Kacang',
                'category' => 'Snack',
                'unit'     => 'pcs',
                'stock'    => 0,
                'price'    => 5000,
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // KATEGORI: Kemasan
        // ═══════════════════════════════════════════════════════════════
        $kemasanItems = [
            [
                'sku'      => 'KMS-001',
                'name'     => 'Mika Cake',
                'category' => 'Kemasan',
                'unit'     => 'pcs',
                'stock'    => 0,
                'price'    => 7000,
            ],
        ];

        // Insert / update semua item berdasarkan SKU
        $allItems = array_merge(
            $atkItems,
            $elektronikItems,
            $bakeryJadiItems,
            $bakeryBahanBakuItems,
            $minumanItems,
            $snackItems,
            $kemasanItems
        );

        foreach ($allItems as $item) {
            Item::updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
}