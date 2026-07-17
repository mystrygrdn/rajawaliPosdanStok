@extends('layouts.app')

@section('title', 'Dashboard Dapur')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">

    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Stok Dapur</h2>
            <p class="text-sm text-slate-400 mt-0.5">Kondisi stok cake & pastry dan bahan baku hari ini.</p>
        </div>
        <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-3 py-1.5">
            🍰 Mode Dapur
        </span>
    </div>

    {{-- 3 Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card card-hover p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-800">{{ $total_items }}</div>
            <div class="text-sm font-semibold text-slate-500 mt-0.5">Produk Bakery</div>
            <div class="text-xs text-slate-400">Jenis barang terdaftar</div>
        </div>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-black text-emerald-600">+{{ $total_inbound_today }}</div>
            <div class="text-sm font-semibold text-slate-500 mt-0.5">Bahan Masuk</div>
            <div class="text-xs text-slate-400">Unit diterima hari ini</div>
        </div>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-black text-rose-500">{{ $total_outbound_today }}</div>
            <div class="text-sm font-semibold text-slate-500 mt-0.5">Bahan Keluar</div>
            <div class="text-xs text-slate-400">Unit keluar hari ini</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('inbounds.create') }}"
            class="card card-hover p-4 flex items-center gap-4 border-2 border-emerald-100 hover:border-emerald-300 transition-all">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-black text-slate-800">Catat Bahan Masuk</div>
                <div class="text-xs text-slate-400 mt-0.5">Terima & tambah stok bahan baku</div>
            </div>
            <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('outbounds.create') }}"
            class="card card-hover p-4 flex items-center gap-4 border-2 border-orange-100 hover:border-orange-300 transition-all">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-black text-slate-800">Catat Pemakaian Bahan</div>
                <div class="text-xs text-slate-400 mt-0.5">Kurangi stok bahan yang terpakai</div>
            </div>
            <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Period Switcher --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h3 class="text-base font-black text-slate-800">Grafik Mutasi Bahan Baku</h3>
        <div class="flex gap-1.5 bg-slate-100 p-1 rounded-xl">
            <a href="{{ route('dashboard', ['period' => 'today']) }}"
                class="text-xs font-bold px-4 py-1.5 rounded-lg transition-all {{ $period === 'today' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Hari Ini</a>
            <a href="{{ route('dashboard', ['period' => 'month']) }}"
                class="text-xs font-bold px-4 py-1.5 rounded-lg transition-all {{ $period === 'month' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Bulan Ini</a>
            <a href="{{ route('dashboard', ['period' => 'year']) }}"
                class="text-xs font-bold px-4 py-1.5 rounded-lg transition-all {{ $period === 'year' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Tahun Ini</a>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-2 h-6 bg-emerald-400 rounded-full"></div>
            <div>
                <div class="text-sm font-black text-slate-800">Mutasi Stok Bakery</div>
                <div class="text-xs text-slate-400">Bahan masuk vs bahan keluar</div>
            </div>
        </div>
        <div style="height:220px; position:relative;">
            <canvas id="stockChart"></canvas>
        </div>
    </div>

    {{-- Tabel Penjualan Cake & Pastry --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    🍰 Penjualan Cake & Pastry Terbaru
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Produk siap jual yang sudah keluar — sebagai acuan produksi.</p>
            </div>
            <a href="{{ route('outbounds.index', ['date_filter' => 'all']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 shrink-0">
    Lihat Riwayat →
</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Nama Produk</th>
                        <th class="px-5 py-3">Sumber</th>
                        <th class="px-5 py-3 text-right">Qty Terjual</th>
                        <th class="px-5 py-3">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse ($recent_sales as $sale)
                    <tr class="table-row transition-all">
                        <td class="px-5 py-3 font-mono text-xs text-slate-400 whitespace-nowrap">{{ $sale['time'] }}</td>
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ $sale['item_name'] }}</td>
                        <td class="px-5 py-3">
                            @if($sale['source'] === 'kasir')
                            <span class="badge bg-amber-50 text-amber-600 border border-amber-100">🛒 POS Kasir</span>
                            @else
                            <span class="badge bg-slate-50 text-slate-500 border border-slate-200">Manual</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <span class="font-black text-rose-500 text-base">-{{ $sale['quantity'] }}</span>
                            <span class="text-xs text-slate-400"> {{ $sale['unit'] }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $sale['operator_name'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center">
                            <div class="text-3xl mb-2">🍰</div>
                            <p class="text-sm font-bold text-slate-400">Belum ada penjualan cake & pastry.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Grid: Mutasi Bahan Baku + Stok Menipis --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        🧴 Mutasi Bahan Baku Terbaru
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Bahan masuk dan bahan yang terpakai.</p>
                </div>
                <a href="{{ route('inbounds.index', ['date_filter' => 'all']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 shrink-0">
    Lihat Riwayat →
</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Bahan</th>
                            <th class="px-5 py-3">Jenis</th>
                            <th class="px-5 py-3 text-right">Qty</th>
                            <th class="px-5 py-3">Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        @forelse ($recent_bahan as $act)
                        <tr class="table-row transition-all">
                            <td class="px-5 py-3 font-mono text-xs text-slate-400 whitespace-nowrap">{{ $act['time'] }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $act['item_name'] }}</td>
                            <td class="px-5 py-3">
                                @if($act['type'] === 'Inbound')
                                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-100">↓ Masuk</span>
                                @else
                                <span class="badge bg-rose-50 text-rose-600 border border-rose-100">↑ Terpakai</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="font-black text-base {{ $act['type'] === 'Inbound' ? 'text-emerald-600' : 'text-rose-500' }}">
                                    {{ $act['type'] === 'Inbound' ? '+' : '-' }}{{ $act['quantity'] }}
                                </span>
                                <span class="text-xs text-slate-400"> {{ $act['unit'] }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $act['operator_name'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <div class="text-3xl mb-2">🧴</div>
                                <p class="text-sm font-bold text-slate-400">Belum ada mutasi bahan baku.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <span class="text-amber-500">⚠</span> Stok Menipis
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Cake, pastry & bahan dengan stok ≤ 10.</p>
            </div>
            <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                @forelse ($low_stock_items as $item)
                <div class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-800 truncate">{{ $item->name }}</div>
                        <span class="text-[10px] font-bold border px-1.5 py-0.5 rounded-lg {{ $item->category_color }}">
                            {{ $item->category_label }}
                        </span>
                    </div>
                    <div class="text-right shrink-0">
                        @if($item->stock == 0)
                        <span class="text-sm font-black text-rose-600">HABIS</span>
                        @else
                        <span class="text-sm font-black text-amber-500">{{ $item->stock }}</span>
                        <span class="text-xs text-slate-400 block">{{ $item->unit }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-5 py-12 text-center">
                    <div class="text-3xl mb-2">✅</div>
                    <p class="text-sm font-bold text-slate-400">Semua stok masih cukup!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels   = @json($chart_labels);
    const inbound  = @json($inbound_data);
    const outbound = @json($outbound_data);
    const fontDefaults = { font: { size: 11, family: 'Inter' }, color: '#94a3b8' };

    new Chart(document.getElementById('stockChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Bahan Masuk', data: inbound, backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 6, borderSkipped: false },
                { label: 'Bahan Keluar', data: outbound, backgroundColor: 'rgba(239,68,68,0.65)', borderRadius: 6, borderSkipped: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11, weight: '700', family: 'Inter' }, padding: 12, boxWidth: 10, boxHeight: 10 } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.raw} unit` } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { ...fontDefaults, stepSize: 1 }, grid: { color: '#f1f5f9' }, title: { display: true, text: 'Unit', ...fontDefaults } },
                x: { ticks: { ...fontDefaults }, grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
@endsection