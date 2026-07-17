<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahan index untuk query yang sering dipakai di DashboardController,
     * InboundController, OutboundController, dan LaporanController.
     *
     * Index yang sudah ada (dari migration sebelumnya):
     *   - items.category
     *   - inbounds.date
     *   - outbounds.date, outbounds.source
     *   - sale_transactions.date
     *
     * Index baru di sini:
     *   - inbounds (item_id, date)  → composite untuk query stok per item per periode
     *   - outbounds (item_id, date) → composite untuk query yang sama di outbound
     *   - outbounds (sale_transaction_id) → lookup outbound per transaksi di nota & laporan
     *   - sale_transactions (user_id, date) → filter kasir per hari di laporan
     *   - items.stock → ORDER BY stock ASC (low stock list di dashboard)
     */
    public function up(): void
    {
        Schema::table('inbounds', function (Blueprint $table) {
            // Composite: dipakai di LaporanController@getStockSummary dan stockByDay
            // "SELECT item_id, SUM(qty) WHERE date < X GROUP BY item_id"
            $table->index(['item_id', 'date'], 'inbounds_item_date_idx');

            // user_id: dipakai di filter operator laporan
            $table->index('user_id', 'inbounds_user_id_idx');
        });

        Schema::table('outbounds', function (Blueprint $table) {
            // Composite: dipakai di LaporanController@getStockSummary dan stockByDay
            $table->index(['item_id', 'date'], 'outbounds_item_date_idx');

            // Lookup outbounds per transaksi: dipakai di CashierController@nota
            // dan di buildPenjualanSheet (Excel export) untuk menampilkan item per struk
            $table->index('sale_transaction_id', 'outbounds_sale_trx_idx');

            // user_id: dipakai di filter operator laporan
            $table->index('user_id', 'outbounds_user_id_idx');
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            // Composite: dipakai di LaporanController filter per kasir + per tanggal
            $table->index(['user_id', 'date'], 'sale_trx_user_date_idx');
        });

        Schema::table('items', function (Blueprint $table) {
            // Dipakai di dashboard low_stock_items: WHERE stock <= 5 ORDER BY stock ASC
            $table->index('stock', 'items_stock_idx');
        });
    }

    public function down(): void
    {
        Schema::table('inbounds', function (Blueprint $table) {
            $table->dropIndex('inbounds_item_date_idx');
            $table->dropIndex('inbounds_user_id_idx');
        });

        Schema::table('outbounds', function (Blueprint $table) {
            $table->dropIndex('outbounds_item_date_idx');
            $table->dropIndex('outbounds_sale_trx_idx');
            $table->dropIndex('outbounds_user_id_idx');
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropIndex('sale_trx_user_date_idx');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_stock_idx');
        });
    }
};