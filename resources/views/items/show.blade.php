@extends('layouts.app')

@section('title', $item->name)
@section('breadcrumb', 'Detail Produk')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('items.index') }}" class="btn-ghost text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <h2 class="text-xl font-black text-slate-800 tracking-tight">Detail Produk</h2>
    </div>

    <div class="card p-6 space-y-5">

        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="badge {{ $item->category_color }} border text-xs mb-2">{{ $item->category_label }}</span>
                <h3 class="text-2xl font-black text-slate-900">{{ $item->name }}</h3>
                <p class="text-xs text-slate-400 font-mono mt-1">SKU: {{ $item->sku }}</p>
            </div>
            <div class="text-right shrink-0">
                <div class="text-2xl font-black text-slate-800">Rp{{ number_format($item->price, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400">per {{ $item->unit }}</div>
            </div>
        </div>

        <hr class="border-slate-100">

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-50 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Stok Saat Ini</div>
                <div class="text-3xl font-black {{ $item->is_critical ? 'text-rose-500' : 'text-emerald-600' }}">
                    {{ number_format($item->stock) }}
                </div>
                <div class="text-xs text-slate-400 font-medium">{{ $item->unit }}</div>
                @if($item->is_critical && $item->stock > 0)
                <div class="mt-2 text-xs font-bold text-amber-500">⚠ Stok kritis</div>
                @elseif($item->stock == 0)
                <div class="mt-2 text-xs font-bold text-rose-500">❌ Stok habis</div>
                @endif
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Nilai Stok</div>
                <div class="text-xl font-black text-indigo-600">
                    Rp{{ number_format($item->stock * $item->price, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 font-medium">total estimasi</div>
            </div>
        </div>

        @if($item->description)
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Deskripsi</div>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $item->description }}</p>
        </div>
        @endif

        @can('admin-only')
        <hr class="border-slate-100">
        <div class="flex gap-3">
            <a href="{{ route('items.edit', $item) }}" class="btn-primary flex-1 justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Produk
            </a>
            <form action="{{ route('items.destroy', $item) }}" method="POST"
                  onsubmit="return confirm('Yakin hapus produk ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
        @endcan
    </div>

</div>
@endsection