<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->enum('payment_method', [
    'cash',
    'qris',
    'transfer'
])->default('cash');
        });
    }

    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};