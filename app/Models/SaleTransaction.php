<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleTransaction extends Model
{
    use HasFactory;

   protected $fillable = [
        'user_id',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'date',
        'daily_no',
        'notes',
    ];

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'change_amount' => 'decimal:2',
        'date'          => 'date',
    ];

    // ── Accessor ─────────────────────────────────────────────────────────

    /** Label metode pembayaran — dipakai di struk dan laporan. */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash'     => 'Tunai',
            'qris'     => 'QRIS',
            'transfer' => 'Transfer Bank',
            default    => ucfirst($this->payment_method),
        };
    }

    // ── Relations ────────────────────────────────────────────────────────

    /** Kasir yang memproses transaksi. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Semua item outbound yang tergabung dalam transaksi ini. */
    public function outbounds(): HasMany
    {
        return $this->hasMany(Outbound::class);
    }
}