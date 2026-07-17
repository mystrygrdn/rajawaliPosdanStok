<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outbound extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'sale_transaction_id',
        'quantity',
        'customer',
        'source',
        'date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date'     => 'date',
    ];

    // ── Accessor ─────────────────────────────────────────────────────────

    /** Label sumber transaksi — dipakai di Blade dan export Excel. */
    public function getSourceLabelAttribute(): string
    {
        return $this->source === 'kasir' ? 'Kasir / POS' : 'Manual';
    }

    /** Warna badge sumber — dipakai di Blade. */
    public function getSourceColorAttribute(): string
    {
        return $this->source === 'kasir'
            ? 'bg-amber-50 text-amber-700 border-amber-200'
            : 'bg-slate-50 text-slate-600 border-slate-200';
    }

    // ── Relations ────────────────────────────────────────────────────────

    /** Relasi balik ke Item. */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** Relasi ke User operator. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Relasi ke header transaksi kasir. */
    public function saleTransaction(): BelongsTo
    {
        return $this->belongsTo(SaleTransaction::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    /** Scope: hanya outbound manual (bukan dari POS). */
    public function scopeManualOnly($query)
    {
        return $query->whereNotIn('source', ['kasir', 'pos']);
    }

    /** Scope: filter berdasarkan tanggal (today, yesterday, atau all). */
    public function scopeDateFilter($query, string $filter)
    {
        return match($filter) {
            'today'     => $query->whereDate('date', now()->toDateString()),
            'yesterday' => $query->whereDate('date', now()->subDay()->toDateString()),
            default     => $query,
        };
    }
}