<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Outbound;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashierController extends Controller
{
    // Timezone WITA — sama dengan LaporanController agar daily_no konsisten
    private const TZ = 'Asia/Makassar';

    public function index()
    {
        $items = Item::where('stock', '>', 0)
                     ->sellableAtPos()
                     ->orderBy('category')
                     ->orderBy('name')
                     ->get();

        return view('cashier.index', compact('items'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'cart'             => ['required', 'array'],
            'cart.*.id'        => ['required', 'exists:items,id'],
            'cart.*.quantity'  => ['required', 'integer', 'min:1'],
            'received_amount'  => ['required', 'numeric', 'min:0'],
            'payment_method'   => ['required', 'in:cash,qris,transfer'],
            'total_amount'     => ['required', 'numeric', 'min:0'],
        ]);

        $cart          = $request->input('cart');
        $totalAmount   = (float) $request->input('total_amount');
        $paidAmount    = (float) $request->input('received_amount');
        $changeAmount  = max(0, $paidAmount - $totalAmount);
        $paymentMethod = $request->input('payment_method');
        $operatorId    = Auth::id();
        $todayWita     = now(self::TZ)->toDateString();

        try {
            $transactionId = null;

            DB::transaction(function () use ($cart, $operatorId, $totalAmount, $paidAmount, $changeAmount, $paymentMethod, $todayWita, &$transactionId) {

                // ── Hitung daily_no secara atomik: kunci baris transaksi hari ini
                //    supaya dua kasir/checkout yang hampir bersamaan tidak dapat
                //    nomor yang sama. ──
                $lastDailyNo = SaleTransaction::whereDate('date', $todayWita)
                    ->lockForUpdate()
                    ->max('daily_no');

                $dailyNo = ($lastDailyNo ?? 0) + 1;

                $sale = SaleTransaction::create([
                    'user_id'        => $operatorId,
                    'total_amount'   => $totalAmount,
                    'paid_amount'    => $paidAmount,
                    'change_amount'  => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'date'           => $todayWita,
                    'daily_no'       => $dailyNo,
                    'notes'          => 'Transaksi POS — Metode: ' . strtoupper($paymentMethod),
                ]);

                $transactionId = $sale->id;

                foreach ($cart as $cartItem) {
                    $item = Item::lockForUpdate()->find($cartItem['id']);

                    if ($item->stock < $cartItem['quantity']) {
                        throw ValidationException::withMessages([
                            'cart' => "Stok '{$item->name}' tidak mencukupi (Tersedia: {$item->stock}).",
                        ]);
                    }

                    $item->decrement('stock', $cartItem['quantity']);

                    Outbound::create([
                        'item_id'             => $item->id,
                        'user_id'             => $operatorId,
                        'sale_transaction_id' => $sale->id,
                        'quantity'            => $cartItem['quantity'],
                        'customer'            => 'Pelanggan Toko',
                        'source'              => 'kasir',
                        'date'                => $todayWita,
                        'notes'               => "No. Transaksi #{$sale->id} | Metode: {$paymentMethod}",
                    ]);
                }
            });

            return response()->json([
                'success'        => true,
                'message'        => 'Transaksi berhasil diproses!',
                'transaction_id' => $transactionId,
                'change_amount'  => $changeAmount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /kasir/nota/{id}
     *
     * Mengembalikan data nota dalam JSON.
     * - created_at dikonversi ke WITA sebagai string (aman untuk JS/PDF)
     * - daily_no: nomor urut harian (reset tiap hari baru, WITA),
     *   dihitung identik dengan LaporanController agar struk ≡ laporan
     */
    public function nota(SaleTransaction $transaction)
    {
        $transaction->load(['user', 'outbounds.item']);

        $witaTime    = $transaction->created_at->setTimezone(self::TZ);
        $witaDateStr = $witaTime->format('d/m/Y H:i');

        return response()->json([
            'id'             => $transaction->id,
            'daily_no'       => $transaction->daily_no,
            'created_at'     => $witaDateStr,
            'date'           => $witaDateStr,
            'operator'       => $transaction->user->name ?? 'Kasir',
            'payment_method' => $transaction->payment_method,
            'total_amount'   => (float) $transaction->total_amount,
            'paid_amount'    => (float) ($transaction->paid_amount ?? $transaction->total_amount),
            'change_amount'  => (float) ($transaction->change_amount ?? 0),
            'items'          => $transaction->outbounds->map(fn ($ob) => [
                'name'     => $ob->item->name ?? '(Produk Dihapus)',
                'qty'      => $ob->quantity,
                'unit'     => $ob->item->unit ?? 'pcs',
                'price'    => (float) ($ob->item->price ?? 0),
                'subtotal' => (float) ($ob->item->price ?? 0) * $ob->quantity,
            ]),
        ]);
    }
}