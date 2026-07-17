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
        // Dikosongkan agar kolom 'description' TIDAK dihapus dari database
        // karena masih aktif digunakan di UI katalog dan Controller.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dikosongkan
    }
};