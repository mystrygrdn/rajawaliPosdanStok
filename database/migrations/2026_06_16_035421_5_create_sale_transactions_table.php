<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel sale_transactions menyimpan header transaksi kasir/POS.
     * Setiap transaksi kasir bisa terdiri dari banyak item (outbounds).
     */
    public function up(): void
    {
        Schema::create('sale_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Kasir yang bertugas
            $table->decimal('total_amount', 15, 2)->default(0);  // Total belanja
            $table->decimal('paid_amount', 15, 2)->default(0);   // Nominal dibayar
            $table->decimal('change_amount', 15, 2)->default(0); // Kembalian
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_transactions');
    }
};
