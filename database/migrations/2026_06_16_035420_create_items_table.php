<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            // Mengubah tipe kolom enum menjadi string biasa agar database-agnostic di level SQLite
            $table->string('category')->default('ATK');
            $table->string('unit', 30)->default('pcs'); // pcs, rim, kg, liter, loyang, botol, dll
            $table->text('description')->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};