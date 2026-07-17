<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SaleTransaction;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->unsignedInteger('daily_no')->nullable()->after('date');
            
            // Composite unique index to speed up reporting and enforce uniqueness per day
            $table->unique(['date', 'daily_no'], 'sale_transactions_date_daily_no_unique');
        });

        // ── Backfill data lama (transaksi yang sudah ada sebelum kolom ini dibuat) ──
        // Hitung ulang daily_no berdasarkan urutan created_at per tanggal WITA,
        // supaya nota lama yang sudah pernah dicetak tetap konsisten nomornya.
        $counter = [];
        SaleTransaction::orderBy('created_at')->get()->each(function ($sale) use (&$counter) {
            $key = $sale->created_at->setTimezone('Asia/Makassar')->format('Y-m-d');
            $counter[$key] = ($counter[$key] ?? 0) + 1;

            DB::table('sale_transactions')
                ->where('id', $sale->id)
                ->update(['daily_no' => $counter[$key]]);
        });
    }

    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropUnique('sale_transactions_date_daily_no_unique');
            $table->dropColumn('daily_no');
        });
    }
};