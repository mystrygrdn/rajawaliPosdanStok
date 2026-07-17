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
        Schema::table('items', function (Blueprint $table) {
            $table->index('category');
        });

        Schema::table('inbounds', function (Blueprint $table) {
            $table->index('date');
        });

        Schema::table('outbounds', function (Blueprint $table) {
            $table->index('date');
            $table->index('source');
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['category']);
        });

        Schema::table('inbounds', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });

        Schema::table('outbounds', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['source']);
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
    }
};
