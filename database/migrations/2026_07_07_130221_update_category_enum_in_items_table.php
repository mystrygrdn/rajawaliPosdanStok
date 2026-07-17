<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * MySQL tidak mendukung ALTER enum lewat Schema Builder Laravel secara
     * langsung, jadi kita pakai raw SQL untuk MODIFY COLUMN.
     */
    public function up(): void
    {
        // Hanya eksekusi kueri ALTER TABLE jika bukan driver SQLite
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE items
                MODIFY COLUMN category ENUM(
                    'ATK',
                    'Elektronik',
                    'Bakery_Jadi',
                    'Bakery_Bahan_Baku',
                    'Snack',
                    'Minuman',
                    'Kemasan'
                ) NOT NULL DEFAULT 'ATK'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE items
                MODIFY COLUMN category ENUM(
                    'ATK',
                    'Elektronik',
                    'Bakery_Jadi',
                    'Bakery_Bahan_Baku'
                ) NOT NULL DEFAULT 'ATK'
            ");
        }
    }
};