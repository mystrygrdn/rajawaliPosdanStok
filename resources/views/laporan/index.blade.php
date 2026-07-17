@extends('layouts.app')

@section('title', 'Laporan')
@section('breadcrumb', 'Laporan & Analitik')

@section('content')
<style>
    /* Styling scrollbar horizontal agar tipis dan tidak menutupi teks */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }
</style>

<div class="space-y-5">

    <div>
        <h2 class="text-xl font-black text-slate-800 tracking-tight">Laporan Mutasi & Stok</h2>
        <p class="text-sm text-slate-400 mt-0.5">Analisis stok dan transaksi berdasarkan periode yang dipilih.</p>
    </div>

    {{-- ============ Filter Form ============ --}}
    <form method="GET" action="{{ route('laporan.index') }}" class="card p-4" x-data="{ periodType: '{{ $periodType }}' }">
        {{-- Row 1: Informasi Periode Aktif & Tombol Ekspor (Aksi Sekunder) --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-3 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode Laporan Aktif:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    <span>📅</span>
                    <span>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                </span>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400 hidden sm:inline uppercase tracking-wider">Ekspor Data:</span>
                <button type="submit" formaction="{{ route('laporan.export.excel') }}" style="background:#16a34a;color:#fff;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;" class="hover:opacity-90 transition-opacity">
                    ⬇ Excel
                </button>
                <button type="submit" formaction="{{ route('laporan.export.pdf') }}" style="background:#dc2626;color:#fff;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;" class="hover:opacity-90 transition-opacity">
                    ⬇ PDF
                </button>
            </div>
        </div>

        {{-- Row 2: Kontrol Filter & Tombol Utama (Aksi Primer) --}}
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label">Jenis Periode</label>
                <select name="period_type" x-model="periodType" class="form-input">
                    <option value="harian">Harian</option>
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                    <option value="custom">Custom (Range Bebas)</option>
                </select>
            </div>
            <div x-show="periodType === 'harian'">
                <label class="form-label">Tanggal</label>
                <input type="date" name="period_date" value="{{ $periodDateValue }}" class="form-input">
            </div>
            <div x-show="periodType === 'mingguan'">
                <label class="form-label">Minggu</label>
                <input type="week" name="period_week" value="{{ $periodWeekValue }}" class="form-input">
            </div>
            <div x-show="periodType === 'bulanan'">
                <label class="form-label">Bulan</label>
                <input type="month" name="period_month" value="{{ $periodMonthValue }}" class="form-input">
            </div>
            <div x-show="periodType === 'tahunan'">
                <label class="form-label">Tahun</label>
                <select name="period_year" class="form-input">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $periodYearValue == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div x-show="periodType === 'custom'">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
            </div>
            <div x-show="periodType === 'custom'">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
            </div>
            <button type="submit" style="background:#7c3aed;color:#fff;font-weight:700;font-size:13px;padding:0 20px;border-radius:10px;border:none;cursor:pointer;height:42px;display:inline-flex;align-items:center;justify-content:center;" class="hover:opacity-90 transition-opacity">
                Tampilkan Laporan
            </button>
        </div>
    </form>

    {{-- ============ Summary Cards ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Total Barang Masuk</div>
            <div class="text-3xl font-black text-emerald-600">+{{ number_format($totalInboundQty) }}</div>
            <div class="text-xs text-emerald-600 font-medium mt-0.5">unit diterima</div>
        </div>
        <div class="card p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Total Barang Keluar</div>
            <div class="text-3xl font-black text-rose-500">{{ number_format($totalOutboundQty) }}</div>
            <div class="text-xs text-rose-500 font-medium mt-0.5">unit dikeluarkan</div>
        </div>
        <div class="card p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Nilai Penjualan (POS)</div>
            <div class="text-xl font-black text-violet-600">Rp{{ number_format($totalOutboundValue, 0, ',', '.') }}</div>
            <div class="text-xs text-violet-600 font-medium mt-0.5">periode ini</div>
        </div>
    </div>

    {{-- ============ Tab Navigation & Content Sections ============ --}}
    <div x-data="{ activeTab: 'stok' }" class="space-y-5">
        
        {{-- Navigation Tabs Grid (Responsive: 2x2 on Mobile, 1x4 on Desktop) --}}
        <div class="bg-slate-100 p-1.5 rounded-xl grid grid-cols-2 md:grid-cols-4 gap-1.5 shadow-sm border border-slate-200">
            <button 
                type="button" 
                @click="activeTab = 'stok'"
                :class="activeTab === 'stok' ? 'bg-white text-violet-700 shadow border border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-2.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2 border"
            >
                <span class="text-sm">📦</span>
                <span>Stok Kategori</span>
            </button>
            <button 
                type="button" 
                @click="activeTab = 'penjualan'"
                :class="activeTab === 'penjualan' ? 'bg-white text-amber-600 shadow border border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-2.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2 border"
            >
                <span class="text-sm">🛒</span>
                <span>Penjualan POS</span>
            </button>
            <button 
                type="button" 
                @click="activeTab = 'masuk'"
                :class="activeTab === 'masuk' ? 'bg-white text-emerald-600 shadow border border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-2.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2 border"
            >
                <span class="text-sm">📥</span>
                <span>Barang Masuk</span>
            </button>
            <button 
                type="button" 
                @click="activeTab = 'keluar'"
                :class="activeTab === 'keluar' ? 'bg-white text-rose-600 shadow border border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="py-2.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2 border"
            >
                <span class="text-sm">📤</span>
                <span>Barang Keluar</span>
            </button>
        </div>

        {{-- Tab Content: Stok Kategori --}}
        <div x-show="activeTab === 'stok'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
            
            {{-- Helper Text for Admin --}}
            <div class="bg-violet-50 border border-violet-100 p-4 rounded-xl">
                <div class="flex gap-3">
                    <span class="text-lg select-none leading-none">💡</span>
                    <div>
                        <h4 class="text-xs font-bold text-violet-800 uppercase tracking-wider">Cara Membaca Ringkasan Stok:</h4>
                        <p class="text-xs text-violet-600 mt-1 leading-relaxed">
                            Di bawah ini adalah ringkasan sisa stok barang per kategori untuk periode yang sedang dipilih (per <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>). 
                            Silakan <strong>klik pada nama kategori</strong> untuk membuka daftar barang lengkap, dan gunakan tombol tanggal di dalamnya untuk melihat pergerakan stok harian.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Card Unified Container: Ringkasan Stok per Kategori --}}
            <div class="card overflow-hidden">
                {{-- Header Card --}}
                <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 bg-violet-600 rounded-full shrink-0"></span>
                        <h3 class="text-sm font-black text-slate-800">
                            Ringkasan Stok per Kategori
                            <span class="text-slate-400 font-medium">— Saldo stok per {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                        </h3>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">
                        Ketuk kategori untuk melihat detail per item
                    </div>
                </div>

                {{-- List Kategori (Dengan Pembatas Tipis) --}}
                <div class="divide-y divide-slate-100">
                    @forelse($stockSummary as $cat)
                    <div
                        class="overflow-hidden bg-white"
                        x-data="stokKategori({
                            category:   '{{ $cat->category }}',
                            label:      '{{ addslashes($cat->category_label) }}',
                            periodType: '{{ $periodType }}',
                            startDate:  '{{ \Illuminate\Support\Str::before($startDate, ' ') ?: substr($startDate, 0, 10) }}',
                            endDate:    '{{ \Illuminate\Support\Str::before($endDate,   ' ') ?: substr($endDate,   0, 10) }}',
                            ajaxUrl:    '{{ route('laporan.stock-by-day') }}'
                        })"
                    >
                        {{-- Header card — klik untuk toggle --}}
                        <button
                            type="button"
                            @click="toggle()"
                            class="w-full px-4 sm:px-5 py-4 flex items-center gap-3 sm:gap-4 hover:bg-slate-50/80 transition-colors text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-inset"
                        >
                            <span class="text-xl leading-none select-none shrink-0">
                                {{ match($cat->category) {
                                    'ATK'               => '📎',
                                    'Elektronik'        => '🔌',
                                    'Bakery_Jadi'       => '🍰',
                                    'Bakery_Bahan_Baku' => '🧴',
                                    'Snack'             => '🍿',
                                    'Minuman'           => '🥤',
                                    'Kemasan'           => '📦',
                                    default             => '📦'
                                } }}
                            </span>

                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-slate-800 truncate">{{ $cat->category_label }}</div>
                                <div class="text-xs text-slate-400 mt-0.5 flex gap-3">
                                    <span class="text-emerald-600 font-semibold">+{{ number_format($cat->mutasi_masuk) }} masuk</span>
                                    <span class="text-rose-500 font-semibold">−{{ number_format($cat->mutasi_keluar) }} keluar</span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-lg sm:text-xl font-black {{ $cat->stok_akhir == 0 ? 'text-rose-500' : 'text-slate-800' }}">
                                    {{ number_format($cat->stok_akhir) }}
                                    <span class="text-xs sm:text-sm font-normal text-slate-400">unit</span>
                                </div>
                            </div>

                            <span class="shrink-0 ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>

                        {{-- ---- Panel Expand ---- --}}
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="border-t border-slate-100"
                        >
                            {{-- Navigasi Hari --}}
                            <template x-if="periodType !== 'harian' && days.length > 0">
                                <div class="px-4 sm:px-5 py-3 bg-slate-50/60 border-b border-slate-100">
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Detail hari:</div>
                                    <div class="flex items-center gap-3 overflow-x-auto pb-3 pt-1 custom-scrollbar" style="-webkit-overflow-scrolling:touch;">
                                        <template x-for="day in days" :key="day.raw">
                                            <button
                                                type="button"
                                                @click="selectDay(day.raw)"
                                                :class="selectedDay === day.raw
                                                    ? 'bg-violet-600 text-white border-violet-600 shadow-sm'
                                                    : 'bg-white text-slate-600 border-slate-200 hover:border-violet-300 hover:text-violet-700'"
                                                class="text-xs font-bold px-4 py-2 rounded-xl border transition-all duration-150 focus:outline-none shrink-0"
                                                x-text="day.label"
                                            ></button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Sub-header: hari yang dipilih + status --}}
                            <div class="px-4 sm:px-5 py-2.5 flex items-center justify-between gap-2 border-b border-slate-50 bg-white">
                                <span class="text-xs text-slate-500 font-medium truncate" x-text="'Data: ' + selectedDayLabel"></span>
                                <span x-show="status === 'loading'" class="flex items-center gap-1.5 text-xs text-violet-500 font-medium shrink-0">
                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                    </svg>
                                    Memuat…
                                </span>
                                <span x-show="status === 'error'" class="flex items-center gap-1.5 text-xs text-rose-500 font-medium shrink-0">
                                    Gagal memuat.
                                    <button @click="retryFetch()" class="underline font-bold hover:text-rose-700">Coba lagi</button>
                                </span>
                            </div>

                            <div x-show="status === 'empty'" class="px-5 py-8 text-center text-sm text-slate-400 font-medium">
                                Tidak ada data stok untuk hari ini.
                            </div>

                            {{-- Tabel stok barang --}}
                            <div class="overflow-x-auto" x-show="status === 'ready'">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="bg-slate-50/80">
                                            <th class="px-5 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-wide">Nama Barang</th>
                                            <th class="px-5 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-wide text-center w-24">Stok Awal</th>
                                            <th class="px-5 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-wide text-center w-24">Masuk</th>
                                            <th class="px-5 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-wide text-center w-24">Keluar</th>
                                            <th class="px-5 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-wide text-center w-28">Stok Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <template x-for="(item, idx) in items" :key="item.name">
                                            <tr :class="idx % 2 === 1 ? 'bg-slate-50/40' : 'bg-white'">
                                                <td class="px-5 py-3 font-semibold text-slate-800" x-text="item.name"></td>

                                                <td class="px-5 py-3 text-center font-mono text-slate-500 tabular-nums"
                                                    x-text="item.stok_awal.toLocaleString('id-ID')"></td>

                                                <td class="px-5 py-3 text-center font-mono font-semibold tabular-nums"
                                                    :class="item.mutasi_masuk > 0 ? 'text-emerald-600' : 'text-slate-300'"
                                                    x-text="item.mutasi_masuk > 0 ? '+' + item.mutasi_masuk.toLocaleString('id-ID') : '—'"></td>

                                                <td class="px-5 py-3 text-center font-mono font-semibold tabular-nums"
                                                    :class="item.mutasi_keluar > 0 ? 'text-rose-500' : 'text-slate-300'"
                                                    x-text="item.mutasi_keluar > 0 ? '−' + item.mutasi_keluar.toLocaleString('id-ID') : '—'"></td>

                                                <td class="px-5 py-3 text-center">
                                                    <span
                                                        class="inline-block font-black text-sm px-2.5 py-0.5 rounded-md tabular-nums"
                                                        :class="{
                                                            'text-rose-600 bg-rose-50 ring-1 ring-rose-200':    item.low_stock === 'empty',
                                                            'text-amber-600 bg-amber-50 ring-1 ring-amber-200': item.low_stock === 'low',
                                                            'text-slate-800':                                    item.low_stock === 'ok'
                                                        }"
                                                        x-text="item.stok_akhir.toLocaleString('id-ID')"
                                                    ></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-violet-50/70 border-t-2 border-violet-100">
                                            <td class="px-5 py-3 text-xs font-bold text-slate-600 uppercase tracking-wide">Total</td>
                                            <td class="px-5 py-3 text-center font-mono font-bold text-slate-700 tabular-nums"
                                                x-text="totals.stok_awal.toLocaleString('id-ID')"></td>
                                            <td class="px-5 py-3 text-center font-mono font-bold text-emerald-600 tabular-nums"
                                                x-text="totals.mutasi_masuk > 0 ? '+' + totals.mutasi_masuk.toLocaleString('id-ID') : '—'"></td>
                                            <td class="px-5 py-3 text-center font-mono font-bold text-rose-500 tabular-nums"
                                                x-text="totals.mutasi_keluar > 0 ? '−' + totals.mutasi_keluar.toLocaleString('id-ID') : '—'"></td>
                                            <td class="px-5 py-3 text-center font-black text-slate-800 tabular-nums"
                                                x-text="totals.stok_akhir.toLocaleString('id-ID')"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>{{-- end expand panel --}}
                    </div>{{-- end card kategori --}}

                    @empty
                    <div class="p-10 text-center">
                        <div class="text-4xl mb-3">📭</div>
                        <div class="text-sm font-bold text-slate-400">Belum ada data stok pada periode ini.</div>
                        <div class="text-xs text-slate-300 mt-1">Coba pilih periode yang berbeda.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tab Content: Detail Penjualan POS --}}
        <div x-show="activeTab === 'penjualan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4" style="display: none;">
            
            {{-- Helper Text for Admin --}}
            <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl">
                <div class="flex gap-3">
                    <span class="text-lg select-none leading-none">💡</span>
                    <div>
                        <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider">Informasi Penjualan POS:</h4>
                        <p class="text-xs text-amber-600 mt-1 leading-relaxed">
                            Berikut adalah daftar seluruh transaksi yang berhasil dicatat lewat aplikasi kasir (POS) toko pada periode terpilih. 
                            Anda dapat melihat rincian barang apa saja yang dibeli pelanggan beserta kasir yang melayani transaksi tersebut.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 bg-amber-500 rounded-full shrink-0"></span>
                        <h3 class="text-sm font-black text-slate-800">
                            Detail Penjualan POS
                            <span class="text-slate-400 font-medium">— {{ $periodLabel }} ({{ $salesCount }} transaksi)</span>
                        </h3>
                    </div>
                    <div class="text-sm">
                        <span class="text-slate-400 font-medium">Grand Total:</span>
                        <span class="font-black text-amber-600 text-base ml-1">Rp{{ number_format($salesTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Tanggal</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Waktu (WITA)</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">No. Transaksi</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Item</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Kasir</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($salesTransactions as $sale)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-400 whitespace-nowrap">{{ $sale->wita_date }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-400 whitespace-nowrap">{{ $sale->wita_time }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">#{{ $sale->daily_no }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-600">
                                    {{ $sale->outbounds->map(fn($ob) => ($ob->item->name ?? '-') . ' (' . $ob->quantity . ')')->implode(', ') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-500 hidden sm:table-cell">{{ $sale->user->name ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-bold text-slate-700">Rp{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400 font-bold">Tidak ada penjualan POS dalam periode ini.</td></tr>
                            @endforelse
                        </tbody>
                        @if($salesCount > 0)
                        <tfoot>
                            <tr class="bg-amber-50/50 border-t border-amber-100">
                                <td colspan="4" class="px-5 py-3 text-right text-xs font-bold text-slate-600 uppercase hidden sm:table-cell">Grand Total Penjualan POS</td>
                                <td colspan="4" class="px-5 py-3 text-right text-xs font-bold text-slate-600 uppercase sm:hidden">Grand Total</td>
                                <td class="px-5 py-3 text-right font-mono text-base font-black text-amber-600">Rp{{ number_format($salesTotal, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab Content: Riwayat Barang Masuk --}}
        <div x-show="activeTab === 'masuk'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4" style="display: none;">
            
            {{-- Helper Text for Admin --}}
            <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl">
                <div class="flex gap-3">
                    <span class="text-lg select-none leading-none">💡</span>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Informasi Riwayat Barang Masuk:</h4>
                        <p class="text-xs text-emerald-600 mt-1 leading-relaxed">
                            Halaman ini memuat daftar stok barang yang ditambahkan ke toko (misalnya ketika Anda menerima kiriman stok baru dari supplier atau distributor). 
                            Digunakan untuk melacak kesesuaian faktur pengiriman barang dengan jumlah fisik yang diterima.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full shrink-0"></span>
                    <h3 class="text-sm font-black text-slate-800">Riwayat Barang Masuk <span class="text-slate-400 font-medium">({{ $inbounds->count() }} transaksi)</span></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Tanggal</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Nama Barang</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Kategori</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Supplier</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase text-center">Qty</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden md:table-cell">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($inbounds as $ib)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($ib->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-sm font-semibold text-slate-800">{{ $ib->item->name ?? '-' }}</td>
                                <td class="px-5 py-3.5 hidden sm:table-cell">
                                    @if($ib->item)
                                    <span class="badge {{ $ib->item->category_color }} border">{{ $ib->item->category_label }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-600 hidden sm:table-cell">{{ $ib->supplier }}</td>
                                <td class="px-5 py-3.5 text-center font-black text-emerald-600 text-base">+{{ $ib->quantity }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-500 hidden md:table-cell">{{ $ib->user->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400 font-bold">Tidak ada data dalam periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab Content: Riwayat Barang Keluar Manual --}}
        <div x-show="activeTab === 'keluar'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4" style="display: none;">
            
            {{-- Helper Text for Admin --}}
            <div class="bg-rose-50 border border-rose-100 p-4 rounded-xl">
                <div class="flex gap-3">
                    <span class="text-lg select-none leading-none">💡</span>
                    <div>
                        <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider">Informasi Riwayat Barang Keluar Manual:</h4>
                        <p class="text-xs text-rose-600 mt-1 leading-relaxed">
                            Berikut adalah daftar pengurangan stok barang yang dicatat secara manual (<strong>bukan dari transaksi kasir POS</strong>). 
                            Biasanya terjadi ketika barang rusak, kedaluwarsa, dipakai untuk keperluan internal toko, atau diretur kembali ke supplier.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <span class="w-3 h-3 bg-rose-500 rounded-full shrink-0"></span>
                    <h3 class="text-sm font-black text-slate-800">Riwayat Barang Keluar Manual <span class="text-slate-400 font-medium">({{ $manualOutbounds->count() }} transaksi)</span></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Tanggal</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Nama Barang</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Kategori</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Penerima</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase text-center">Qty</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase text-right hidden md:table-cell">Nilai</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden md:table-cell">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($manualOutbounds as $ob)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($ob->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-sm font-semibold text-slate-800">{{ $ob->item->name ?? '-' }}</td>
                                <td class="px-5 py-3.5 hidden sm:table-cell">
                                    @if($ob->item)
                                    <span class="badge {{ $ob->item->category_color }} border">{{ $ob->item->category_label }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-600 hidden sm:table-cell">{{ $ob->customer ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-center font-black text-rose-500 text-base">-{{ $ob->quantity }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-bold text-slate-700 hidden md:table-cell">
                                    Rp{{ number_format(($ob->item->price ?? 0) * $ob->quantity, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-500 hidden md:table-cell">{{ $ob->user->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400 font-bold">Tidak ada data dalam periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ============================================================
     ALPINE.JS COMPONENT — stokKategori
     ============================================================ --}}
<script>
function parseLocalDate(str) {
    const s = (str || '').slice(0, 10);
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function toIsoLocal(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/**
 * Satu-satunya tempat penggabungan barang dengan nama sama dilakukan.
 * Tidak peduli backend kirim apa (urutan, duplikat, dsb), fungsi ini
 * SELALU menghasilkan tepat 1 baris per nama barang.
 */
function mergeStockItemsByName(rawItems) {
    const map = new Map();

    for (const item of (rawItems || [])) {
        const key = String(item.name || '')
            .replace(/\u00A0/g, ' ')   // non-breaking space -> spasi biasa
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();

        if (!map.has(key)) {
            map.set(key, {
                name: item.name,
                stok_awal: 0,
                mutasi_masuk: 0,
                mutasi_keluar: 0,
                stok_akhir: 0,
                ending_value: 0,
            });
        }

        const acc = map.get(key);
        acc.stok_awal     += Number(item.stok_awal)     || 0;
        acc.mutasi_masuk  += Number(item.mutasi_masuk)  || 0;
        acc.mutasi_keluar += Number(item.mutasi_keluar) || 0;
        acc.stok_akhir    += Number(item.stok_akhir)    || 0;
        acc.ending_value  += Number(item.ending_value)  || 0;
    }

    const merged = Array.from(map.values());
    merged.forEach(i => {
        i.low_stock = i.stok_akhir === 0 ? 'empty' : (i.stok_akhir <= 5 ? 'low' : 'ok');
    });
    merged.sort((a, b) => a.name.localeCompare(b.name));
    return merged;
}

/**
 * Total selalu dihitung dari array yang SAMA dengan yang dirender di tabel
 * — supaya baris TOTAL tidak mungkin beda dengan jumlah baris yang tampil.
 */
function sumStockTotals(items) {
    return items.reduce((acc, i) => {
        acc.stok_awal     += i.stok_awal;
        acc.mutasi_masuk  += i.mutasi_masuk;
        acc.mutasi_keluar += i.mutasi_keluar;
        acc.stok_akhir    += i.stok_akhir;
        acc.ending_value  += i.ending_value;
        return acc;
    }, { stok_awal: 0, mutasi_masuk: 0, mutasi_keluar: 0, stok_akhir: 0, ending_value: 0 });
}

function stokKategori({ category, label, periodType, startDate, endDate, ajaxUrl }) {
    const safeStart = (startDate || '').slice(0, 10);
    const safeEnd   = (endDate   || '').slice(0, 10);

    return {
        open:             false,
        status:           'idle', // idle | loading | error | empty | ready
        category,
        label,
        periodType,
        startDate:        safeStart,
        endDate:          safeEnd,
        ajaxUrl,
        items:            [],
        totals:           { stok_awal: 0, mutasi_masuk: 0, mutasi_keluar: 0, stok_akhir: 0, ending_value: 0 },
        days:             [],
        selectedDay:      '',
        selectedDayLabel: '',

        toggle() {
            this.open = !this.open;
            if (this.open && this.status === 'idle') {
                this.initLoad();
            }
        },

        initLoad() {
            this.buildDays();

            const defaultDay = (this.periodType === 'harian')
                ? this.startDate
                : (this.days.length > 0 ? this.days[this.days.length - 1].raw : this.endDate);

            this.selectDay(defaultDay);
        },

        buildDays() {
            if (this.periodType === 'harian') {
                this.days = [];
                return;
            }

            const start = parseLocalDate(this.startDate);
            const end   = parseLocalDate(this.endDate);
            const HARI  = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
            const days  = [];
            const cur   = new Date(start);

            while (cur <= end) {
                const iso = toIsoLocal(cur);
                const tgl = String(cur.getDate()).padStart(2, '0');
                const bln = String(cur.getMonth() + 1).padStart(2, '0');
                days.push({ raw: iso, label: HARI[cur.getDay()] + ' ' + tgl + '/' + bln });
                cur.setDate(cur.getDate() + 1);
            }

            // Jika periode tahunan atau data lebih dari 31 hari, batasi ke 31 hari terakhir agar performa tetap cepat.
            // Untuk bulanan atau mingguan, tampilkan seluruh hari secara lengkap.
            if (this.periodType === 'tahunan' || days.length > 31) {
                this.days = days.slice(-31);
            } else {
                this.days = days;
            }
        },

        async selectDay(dateStr) {
            const safe = (dateStr || '').slice(0, 10);
            this.selectedDay = safe;

            const d     = parseLocalDate(safe);
            const BULAN = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            this.selectedDayLabel = HARI[d.getDay()] + ', ' + d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear();

            await this.fetchDay(safe);
        },

        retryFetch() {
            this.fetchDay(this.selectedDay);
        },

        async fetchDay(dateStr) {
            this.status = 'loading';
            this.items  = [];
            this.totals = { stok_awal: 0, mutasi_masuk: 0, mutasi_keluar: 0, stok_akhir: 0, ending_value: 0 };

            try {
                const url = new URL(this.ajaxUrl, window.location.origin);
                url.searchParams.set('date',     dateStr);
                url.searchParams.set('category', this.category);

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    }
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();

                // Gabungkan per nama barang DI SINI — satu-satunya tempat ini terjadi.
                this.items  = mergeStockItemsByName(data.items ?? []);
                this.totals = sumStockTotals(this.items);
                this.status = this.items.length > 0 ? 'ready' : 'empty';

            } catch (err) {
                console.error('[stokKategori] fetch error:', err);
                this.items  = [];
                this.status = 'error';
            }
        },
    };
}
</script>
@endsection