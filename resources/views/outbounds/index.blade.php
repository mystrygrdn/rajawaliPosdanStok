@extends('layouts.app')

@section('title', 'Barang Keluar')
@section('breadcrumb', 'Barang Keluar')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Riwayat Barang Keluar</h2>
            <p class="text-sm text-slate-400 mt-0.5">
                @if(auth()->user()->role === 'dapur')
                    Log pemakaian bahan baku dapur.
                @else
                    Log semua barang yang keluar dari gudang.
                @endif
            </p>
        </div>
        @can('admin-or-dapur')
        <a href="{{ route('outbounds.create') }}" style="background:#e11d48;color:#fff;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            @if(auth()->user()->role === 'dapur')
                Catat Pemakaian Bahan
            @else
                Catat Keluar Manual
            @endif
        </a>
        @endcan
    </div>

    {{-- Stat Cards — konsisten dengan inbounds/index --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($total_outbound_today) }}</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Keluar Hari Ini</div>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($total_outbound_yesterday) }}</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Keluar Kemarin</div>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($total_outbound_all) }}</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Semua</div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role !== 'dapur')
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-700 font-medium">
            <strong>Catatan:</strong> Barang keluar dari POS Kasir dicatat otomatis. Tombol "Catat Keluar Manual" digunakan untuk transaksi non-kasir seperti barang rusak, return, atau pemakaian internal.
        </p>
    </div>
    @else
    <div class="bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-4 h-4 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-orange-700 font-medium">
            Catat bahan baku yang <strong>terpakai dalam proses produksi</strong>. Stok akan otomatis berkurang.
        </p>
    </div>
    @endif

    <form method="GET" action="{{ route('outbounds.index') }}" class="card p-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-45">
                <label class="form-label">Cari Nama Barang</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama barang..." class="form-input">
            </div>
            <div class="min-w-40">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select">
                    <option value="all">Semua Kategori</option>
                    @foreach($allowedCategories as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>
                        {{ match($cat) {
    'ATK'               => '📎 ATK',
    'Elektronik'        => '🔌 Elektronik',
    'Bakery_Jadi'       => '🍰 Cake & Pastry',
    'Bakery_Bahan_Baku' => '🧴 Bahan Baku',
    'Minuman'           => '🥤 Minuman',
    'Snack'             => '🍿 Snack',
    'Kemasan'           => '📦 Kemasan',
    default             => $cat,
} }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->role !== 'dapur')
            <div class="min-w-35">
                <label class="form-label">Sumber</label>
                <select name="source" class="form-select">
                    <option value="all">Semua Sumber</option>
                    <option value="manual" {{ $source === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="kasir" {{ $source === 'kasir' ? 'selected' : '' }}>POS Kasir</option>
                </select>
            </div>
            @endif
            <div class="min-w-35">
                <label class="form-label">Tanggal</label>
                <select name="date_filter" class="form-select">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>🕘 Riwayat Semua</option>
                </select>
            </div>
            <button type="submit" style="background:#e11d48;color:#fff;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;">Filter</button>
            @if($search || ($category && $category !== 'all') || ($source && $source !== 'all') || $dateFilter !== 'today')
            <a href="{{ route('outbounds.index') }}" class="btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-black text-slate-800">Log Barang Keluar</h3>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $outbounds->total() }} record ditemukan
                @if($dateFilter === 'today') — Hari Ini
                @elseif($dateFilter === 'yesterday') — Kemarin
                @elseif($dateFilter === 'all') — Riwayat Semua
                @endif
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Nama Barang</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">Kategori</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase">
                            {{ auth()->user()->role === 'dapur' ? 'Keterangan Pemakaian' : 'Penerima' }}
                        </th>
                        @if(auth()->user()->role !== 'dapur')
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Sumber</th>
                        @endif
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase text-center">Qty Keluar</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Operator</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden md:table-cell">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($outbounds as $ob)
                    <tr class="table-row">
                        <td class="px-5 py-3.5 font-mono text-xs text-slate-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($ob->date)->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm font-semibold text-slate-800">{{ $ob->item->name ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            @if($ob->item)
                            <span class="badge {{ $ob->item->category_color }} border">{{ $ob->item->category_label }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-slate-600">{{ $ob->customer ?? '-' }}</td>
                        @if(auth()->user()->role !== 'dapur')
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            @if($ob->source === 'kasir')
                            <span class="badge bg-amber-50 text-amber-600 border border-amber-100">🛒 POS Kasir</span>
                            @else
                            <span class="badge bg-slate-50 text-slate-500 border border-slate-200">Manual</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-black text-rose-500 text-base">-{{ $ob->quantity }}</span>
                            <span class="text-xs text-slate-400"> {{ $ob->item->unit ?? 'pcs' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-slate-500 hidden sm:table-cell">{{ $ob->user->name ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-400 max-w-xs truncate hidden md:table-cell">{{ $ob->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'dapur' ? '7' : '8' }}" class="px-5 py-14 text-center">
                            <div class="text-4xl mb-3">📤</div>
                            <p class="text-sm font-bold text-slate-400">Belum ada transaksi barang keluar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($outbounds->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $outbounds->links() }}
        </div>
        @endif
    </div>
</div>
@endsection