@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')


@section('content')
<div class="space-y-6" x-data>

    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Ringkasan Bisnis</h2>
            <p class="text-sm text-slate-400 mt-0.5">Gambaran menyeluruh performa toko hari ini.</p>
        </div>
        <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold px-4 py-2.5 rounded-xl h-fit">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Khusus Owner Toko
        </div>
    </div>

    {{-- 4 Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-800">{{ $total_items }}</div>
            <div class="text-sm font-semibold text-slate-500 mt-0.5">Total Produk</div>
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
            <div class="text-sm font-semibold text-slate-500 mt-0.5">Barang Masuk</div>
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
            <div class="text-sm font-semibold text-slate-500 mt-0.5">Barang Keluar</div>
            <div class="text-xs text-slate-400">Unit keluar hari ini</div>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-orange-500 p-5 rounded-2xl shadow-sm shadow-amber-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl font-black text-white leading-tight">Rp{{ number_format($revenue_today, 0, ',', '.') }}</div>
            <div class="text-sm font-semibold text-amber-100 mt-0.5">Pendapatan</div>
            <div class="text-xs text-amber-200">Total penjualan hari ini</div>
        </div>
    </div>

    {{-- Period Switcher --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h3 class="text-base font-black text-slate-800">Grafik Aktivitas Toko</h3>
        <div class="flex gap-1.5 bg-slate-100 p-1 rounded-xl">
            <a href="{{ route('dashboard', ['period' => 'today']) }}"
                class="text-xs font-bold px-4 py-1.5 rounded-lg transition-all {{ $period === 'today' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Hari Ini
            </a>
            <a href="{{ route('dashboard', ['period' => 'month']) }}"
                class="text-xs font-bold px-4 py-1.5 rounded-lg transition-all {{ $period === 'month' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Bulan Ini
            </a>
            <a href="{{ route('dashboard', ['period' => 'year']) }}"
                class="text-xs font-bold px-4 py-1.5 rounded-lg transition-all {{ $period === 'year' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Tahun Ini
            </a>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-6 bg-amber-400 rounded-full"></div>
                <div>
                    <div class="text-sm font-black text-slate-800">Grafik Pendapatan</div>
                    <div class="text-xs text-slate-400">Nilai penjualan per periode</div>
                </div>
            </div>
            <div style="height:200px; position:relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-6 bg-emerald-400 rounded-full"></div>
                <div>
                    <div class="text-sm font-black text-slate-800">Grafik Mutasi Stok</div>
                    <div class="text-xs text-slate-400">Barang masuk vs keluar</div>
                </div>
            </div>
            <div style="height:200px; position:relative;">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bottom Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-800">Aktivitas Terakhir</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Mutasi stok terbaru yang dicatat sistem.</p>
                </div>
              <a href="{{ route('inbounds.index', ['date_filter' => 'all']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Lihat Riwayat →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Barang</th>
                            <th class="px-5 py-3">Jenis</th>
                            <th class="px-5 py-3 text-right">Qty</th>
                            <th class="px-5 py-3">Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        @forelse ($recent_activities as $act)
                        <tr class="table-row transition-all">
                            <td class="px-5 py-3 font-mono text-xs text-slate-400 whitespace-nowrap">{{ $act['time'] }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $act['item_name'] }}</td>
                            <td class="px-5 py-3">
                                @if($act['type'] === 'Inbound')
                                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-100">↓ Masuk</span>
                                @else
                                <span class="badge bg-rose-50 text-rose-600 border border-rose-100">↑ Keluar</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-black {{ $act['type'] === 'Inbound' ? 'text-emerald-600' : 'text-rose-500' }}">
                                {{ $act['type'] === 'Inbound' ? '+' : '-' }}{{ $act['quantity'] }}
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $act['operator_name'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="text-3xl mb-2">📋</div>
                                <p class="text-sm font-bold text-slate-400">Belum ada aktivitas tercatat.</p>
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
                <p class="text-xs text-slate-400 mt-0.5">Produk dengan stok ≤ 5 unit.</p>
            </div>
            <div class="divide-y divide-slate-50 max-h-72 overflow-y-auto">
                @forelse ($low_stock_items as $item)
                <div class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-800 truncate">{{ $item->name }}</div>
                        <span class="text-[10px] font-bold border px-1.5 py-0.5 rounded-lg {{ $item->category_color }}">{{ $item->category_label }}</span>
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
                    <p class="text-sm font-bold text-slate-400">Semua stok aman!</p>
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
    const revenue  = @json($revenue_data);
    const fontDefaults = { font: { size: 11, family: 'Inter' }, color: '#94a3b8' };
    const gridColor = '#f1f5f9';

    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'line',
        data: { labels, datasets: [{ label: 'Pendapatan (Rp)', data: revenue, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.08)', pointBackgroundColor: '#f59e0b', pointRadius: 4, pointHoverRadius: 6, borderWidth: 2.5, fill: true, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' Rp' + Number(ctx.raw).toLocaleString('id-ID') } } }, scales: { y: { beginAtZero: true, ticks: { ...fontDefaults, callback: v => 'Rp' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v >= 1000 ? (v/1000).toFixed(0)+'rb' : v)) }, grid: { color: gridColor } }, x: { ticks: { ...fontDefaults }, grid: { display: false } } } }
    });

    new Chart(document.getElementById('stockChart').getContext('2d'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Barang Masuk', data: inbound, backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 6, borderSkipped: false }, { label: 'Barang Keluar', data: outbound, backgroundColor: 'rgba(239,68,68,0.65)', borderRadius: 6, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'top', labels: { font: { size: 11, weight: '700', family: 'Inter' }, padding: 12, boxWidth: 10, boxHeight: 10 } }, tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.raw} unit` } } }, scales: { y: { beginAtZero: true, ticks: { ...fontDefaults, stepSize: 1 }, grid: { color: gridColor }, title: { display: true, text: 'Unit', ...fontDefaults } }, x: { ticks: { ...fontDefaults }, grid: { display: false } } } }
    });
});
</script>
@endpush
@endsection