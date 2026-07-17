<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // sale_transaction_id: nullable integer (no FK constraint for SQLite compatibility)
            $table->unsignedBigInteger('sale_transaction_id')->nullable();
            $table->integer('quantity');
            $table->string('customer')->nullable(); // nullable: kasir tidak perlu isi customer
            $table->enum('source', ['manual', 'kasir'])->default('manual');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbounds');
    }
};