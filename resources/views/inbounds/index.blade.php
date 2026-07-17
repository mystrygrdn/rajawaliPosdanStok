@extends('layouts.app')

@section('title', 'Barang Masuk')
@section('breadcrumb', 'Barang Masuk')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Riwayat Barang Masuk</h2>
            <p class="text-sm text-slate-400 mt-0.5">
                @auth
                    @if(auth()->user()->role === 'dapur')
                        Log penerimaan bahan baku dapur.
                    @else
                        Log semua penerimaan stok ke gudang.
                    @endif
                @endauth
            </p>
        </div>
        @can('admin-or-dapur')
        <a href="{{ route('inbounds.create') }}" class="btn-emerald">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            @if(auth()->user()->role === 'dapur')
                Catat Bahan Masuk
            @else
                Catat Barang Masuk
            @endif
        </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Hari Ini</div>
            <div class="text-2xl font-black text-emerald-600">+{{ number_format($total_inbound_today) }}</div>
            <div class="text-xs text-emerald-600 font-medium">unit masuk</div>
        </div>
        <div class="card p-4">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Kemarin</div>
            <div class="text-2xl font-black text-slate-600">+{{ number_format($total_inbound_yesterday) }}</div>
            <div class="text-xs text-slate-400 font-medium">unit masuk</div>
        </div>
        <div class="card p-4">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Total Semua</div>
            <div class="text-2xl font-black text-indigo-600">{{ number_format($total_inbound_all) }}</div>
            <div class="text-xs text-indigo-600 font-medium">unit akumulasi</div>
        </div>
    </div>

    <form method="GET" action="{{ route('inbounds.index') }}" class="card p-4">
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
            <div class="min-w-35">
                <label class="form-label">Tanggal</label>
                <select name="date_filter" class="form-select">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>🕘 Riwayat Semua</option>
                </select>
            </div>
            <button type="submit" class="btn-emerald">Filter</button>
            @if($search || ($category && $category !== 'all') || $dateFilter !== 'today')
            <a href="{{ route('inbounds.index') }}" class="btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-black text-slate-800">Log Barang Masuk</h3>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $inbounds->total() }} record
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
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Supplier</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase text-center">Qty Masuk</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden sm:table-cell">Operator</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase hidden md:table-cell">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($inbounds as $ib)
                    <tr class="table-row">
                        <td class="px-5 py-3.5 font-mono text-xs text-slate-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($ib->date)->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm font-semibold text-slate-800">{{ $ib->item->name ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            @if($ib->item)
                            <span class="badge {{ $ib->item->category_color }} border">{{ $ib->item->category_label }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-slate-600">{{ $ib->supplier ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-black text-emerald-600 text-base">+{{ $ib->quantity }}</span>
                            <span class="text-xs text-slate-400"> {{ $ib->item->unit ?? 'pcs' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-slate-500 hidden sm:table-cell">{{ $ib->user->name ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-400 max-w-xs truncate hidden md:table-cell">{{ $ib->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-14 text-center">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="text-sm font-bold text-slate-400">
                                Belum ada transaksi barang masuk
                                @if($dateFilter === 'today') hari ini @elseif($dateFilter === 'yesterday') kemarin @endif.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inbounds->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $inbounds->links() }}
        </div>
        @endif
    </div>
</div>
@endsection