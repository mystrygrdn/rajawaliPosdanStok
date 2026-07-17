<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'unit',
        'description',
        'stock',
        'price',
    ];

    protected $casts = [
        'stock' => 'integer',
        'price' => 'decimal:2',
    ];

    // ── Auto-append accessor sering dipakai di Blade ────────────────────
    protected $appends = [
        'category_label',
        'category_color',
        'is_critical',
    ];

    /**
     * Daftar kategori yang TIDAK boleh dijual di Kasir/POS
     * (bahan baku dapur, dsb). Satu-satunya tempat pengaturan ini —
     * kalau nanti ada kategori bahan baku baru, tinggal tambahkan di sini.
     */
    public const NON_SELLABLE_CATEGORIES = [
        'Bakery_Bahan_Baku',
    ];

    // ── Accessors ────────────────────────────────────────────────────────

    /** Label tampilan per kategori. */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'ATK'               => 'ATK',
            'Elektronik'        => 'Elektronik',
            'Bakery_Jadi'       => 'Cake & Pastry',
            'Bakery_Bahan_Baku' => 'Bahan Baku',
            'Snack'             => 'Snack',
            'Minuman'           => 'Minuman',
            'Kemasan'           => 'Kemasan',
            default             => $this->category,
        };
    }

    /** Warna badge Tailwind per kategori. */
    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'ATK'               => 'bg-blue-50 text-blue-700 border-blue-200',
            'Elektronik'        => 'bg-violet-50 text-violet-700 border-violet-200',
            'Bakery_Jadi'       => 'bg-amber-50 text-amber-700 border-amber-200',
            'Bakery_Bahan_Baku' => 'bg-orange-50 text-orange-700 border-orange-200',
            'Snack'             => 'bg-lime-50 text-lime-700 border-lime-200',
            'Minuman'           => 'bg-sky-50 text-sky-700 border-sky-200',
            'Kemasan'           => 'bg-stone-50 text-stone-700 border-stone-200',
            default             => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    }

    /** Emoji ikon per kategori — dipakai di POS & Laporan supaya konsisten. */
    public function getCategoryEmojiAttribute(): string
    {
        return match($this->category) {
            'ATK'               => '📎',
            'Elektronik'        => '🔌',
            'Bakery_Jadi'       => '🍰',
            'Bakery_Bahan_Baku' => '🧴',
            'Snack'             => '🍿',
            'Minuman'           => '🥤',
            'Kemasan'           => '📦',
            default             => '📦',
        };
    }

    /** Apakah stok dalam kondisi kritis (<= 5). */
    public function getIsCriticalAttribute(): bool
    {
        return $this->stock <= 5;
    }

    // ── Scopes — reusable query filter ───────────────────────────────────

    /** Scope: hanya produk kategori bakery (dapur). */
    public function scopeBakery($query)
    {
        return $query->whereIn('category', ['Bakery_Jadi', 'Bakery_Bahan_Baku']);
    }

    /** Scope: produk dengan stok kritis (≤ threshold, default 5). */
    public function scopeLowStock($query, int $threshold = 5)
    {
        return $query->where('stock', '<=', $threshold);
    }

    /** Scope: filter per kategori (skip jika 'all' atau kosong). */
    public function scopeOfCategory($query, ?string $category)
    {
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        return $query;
    }

    /**
     * Scope: hanya kategori yang boleh dijual di Kasir/POS.
     * Snack, Minuman, dan Kemasan otomatis ikut karena tidak
     * termasuk NON_SELLABLE_CATEGORIES.
     */
    public function scopeSellableAtPos($query)
    {
        return $query->whereNotIn('category', self::NON_SELLABLE_CATEGORIES);
    }

    // ── Relations ────────────────────────────────────────────────────────

    /** Relasi HasMany ke riwayat barang masuk. */
    public function inbounds(): HasMany
    {
        return $this->hasMany(Inbound::class);
    }

    /** Relasi HasMany ke riwayat barang keluar. */
    public function outbounds(): HasMany
    {
        return $this->hasMany(Outbound::class);
    }
}