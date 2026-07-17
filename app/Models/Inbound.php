<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inbound extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'quantity',
        'supplier',
        'date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date'     => 'date',   // ← ditambahkan: konsisten dengan Outbound
    ];

    // ── Relations ────────────────────────────────────────────────────────

    /** Relasi balik ke Item (SKU). */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** Relasi ke User pengisi transaksi. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────

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