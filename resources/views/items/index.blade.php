@extends('layouts.app')

@section('title', auth()->user()->isDapur() ? 'Produk Bakery' : 'Katalog Produk')
@section('breadcrumb', auth()->user()->isDapur() ? 'Produk Bakery' : 'Katalog Produk')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">
                {{ auth()->user()->isDapur() ? 'Produk Bakery' : 'Katalog Produk' }}
            </h2>
            <p class="text-sm text-slate-400 mt-0.5">
                {{ auth()->user()->isDapur() ? 'Daftar cake & pastry dan bahan baku dapur.' : 'Daftar semua barang yang terdaftar di sistem.' }}
            </p>
        </div>
        @can('admin-only')
        <a href="{{ route('items.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk Baru
        </a>
        @endcan
    </div>

    {{-- Stats Cards per Kategori --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $catStats = $items->groupBy('category');
            $cats = auth()->user()->isDapur()
                ? [
                    'Bakery_Jadi'       => ['icon' => '🍰', 'label' => 'Cake & Pastry', 'color' => 'text-amber-600 bg-amber-50 border-amber-100'],
                    'Bakery_Bahan_Baku' => ['icon' => '🧴', 'label' => 'Bahan Baku',    'color' => 'text-orange-600 bg-orange-50 border-orange-100'],
                    'Kemasan'           => ['icon' => '📦', 'label' => 'Kemasan',       'color' => 'text-fuchsia-600 bg-fuchsia-50 border-fuchsia-100'],
                ]
                : (auth()->user()->role === 'admin'
                    ? [
                        // Menyembunyikan Bahan Baku dari grid card Admin
                        'ATK'               => ['icon' => '📎', 'label' => 'ATK',           'color' => 'text-blue-600 bg-blue-50 border-blue-100'],
                        'Elektronik'        => ['icon' => '🔌', 'label' => 'Elektronik',    'color' => 'text-violet-600 bg-violet-50 border-violet-100'],
                        'Bakery_Jadi'       => ['icon' => '🍰', 'label' => 'Cake & Pastry', 'color' => 'text-amber-600 bg-amber-50 border-amber-100'],
                        'Minuman'           => ['icon' => '🥤', 'label' => 'Minuman',       'color' => 'text-cyan-600 bg-cyan-50 border-cyan-100'],
                        'Snack'             => ['icon' => '🍿', 'label' => 'Snack',         'color' => 'text-lime-600 bg-lime-50 border-lime-100'],
                        'Kemasan'           => ['icon' => '📦', 'label' => 'Kemasan',       'color' => 'text-fuchsia-600 bg-fuchsia-50 border-fuchsia-100'],
                    ]
                    : [
                        'ATK'               => ['icon' => '📎', 'label' => 'ATK',           'color' => 'text-blue-600 bg-blue-50 border-blue-100'],
                        'Elektronik'        => ['icon' => '🔌', 'label' => 'Elektronik',    'color' => 'text-violet-600 bg-violet-50 border-violet-100'],
                        'Bakery_Jadi'       => ['icon' => '🍰', 'label' => 'Cake & Pastry', 'color' => 'text-amber-600 bg-amber-50 border-amber-100'],
                        'Bakery_Bahan_Baku' => ['icon' => '🧴', 'label' => 'Bahan Baku',    'color' => 'text-orange-600 bg-orange-50 border-orange-100'],
                        'Minuman'           => ['icon' => '🥤', 'label' => 'Minuman',       'color' => 'text-cyan-600 bg-cyan-50 border-cyan-100'],
                        'Snack'             => ['icon' => '🍿', 'label' => 'Snack',         'color' => 'text-lime-600 bg-lime-50 border-lime-100'],
                        'Kemasan'           => ['icon' => '📦', 'label' => 'Kemasan',       'color' => 'text-fuchsia-600 bg-fuchsia-50 border-fuchsia-100'],
                    ]);
        @endphp
        @foreach($cats as $catKey => $catMeta)
        @php $group = $catStats->get($catKey, collect()); @endphp
        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl">{{ $catMeta['icon'] }}</span>
                <span class="badge {{ $catMeta['color'] }} border">{{ $group->count() }} produk</span>
            </div>
            <div class="text-xl font-black text-slate-800">{{ number_format($group->sum('stock')) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">{{ $catMeta['label'] }} — total stok</div>
        </div>
        @endforeach
    </div>

    {{-- Search + Filter --}}
    <div class="card p-4 flex flex-col sm:flex-row gap-3 items-center">
        <form method="GET" action="{{ route('items.index') }}" class="flex gap-2 flex-1 w-full">
            <input type="hidden" name="category" value="{{ $category }}">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk..."
                    class="form-input pl-10">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <button type="submit" class="btn-primary">Cari</button>
            @if($search)
            <a href="{{ route('items.index', ['category' => $category]) }}" class="btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    {{-- Category Tabs --}}
    @php
        $tabs = auth()->user()->isDapur()
            ? [
                'all'               => ['label' => 'Semua Bakery',  'active' => 'bg-slate-800 text-white',   'inactive' => 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'],
                'Bakery_Jadi'       => ['label' => 'Cake & Pastry', 'active' => 'bg-amber-500 text-white',   'inactive' => 'bg-white border border-amber-200 text-amber-600 hover:bg-amber-50'],
                'Bakery_Bahan_Baku' => ['label' => 'Bahan Baku',    'active' => 'bg-orange-500 text-white',  'inactive' => 'bg-white border border-orange-200 text-orange-600 hover:bg-amber-50'],
                'Kemasan'           => ['label' => 'Kemasan',       'active' => 'bg-fuchsia-500 text-white', 'inactive' => 'bg-white border border-fuchsia-200 text-fuchsia-600 hover:bg-amber-50'],
            ]
            : (auth()->user()->role === 'admin'
                ? [
                    // Menyembunyikan tab Bahan Baku dari Admin
                    'all'               => ['label' => 'Semua Produk',  'active' => 'bg-slate-800 text-white',   'inactive' => 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'],
                    'ATK'               => ['label' => 'ATK',           'active' => 'bg-blue-600 text-white',    'inactive' => 'bg-white border border-blue-200 text-blue-600 hover:bg-slate-50'],
                    'Elektronik'        => ['label' => 'Elektronik',    'active' => 'bg-violet-600 text-white',  'inactive' => 'bg-white border border-violet-200 text-violet-600 hover:bg-slate-50'],
                    'Bakery_Jadi'       => ['label' => 'Cake & Pastry', 'active' => 'bg-amber-500 text-white',   'inactive' => 'bg-white border border-amber-200 text-amber-600 hover:bg-slate-50'],
                    'Minuman'           => ['label' => 'Minuman',       'active' => 'bg-cyan-500 text-white',    'inactive' => 'bg-white border border-cyan-200 text-cyan-600 hover:bg-slate-50'],
                    'Snack'             => ['label' => 'Snack',         'active' => 'bg-lime-500 text-white',    'inactive' => 'bg-white border border-lime-200 text-lime-600 hover:bg-slate-50'],
                    'Kemasan'           => ['label' => 'Kemasan',       'active' => 'bg-fuchsia-500 text-white', 'inactive' => 'bg-white border border-fuchsia-200 text-fuchsia-600 hover:bg-slate-50'],
                ]
                : [
                    'all'               => ['label' => 'Semua Produk',  'active' => 'bg-slate-800 text-white',   'inactive' => 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'],
                    'ATK'               => ['label' => 'ATK',           'active' => 'bg-blue-600 text-white',    'inactive' => 'bg-white border border-blue-200 text-blue-600 hover:bg-slate-50'],
                    'Elektronik'        => ['label' => 'Elektronik',    'active' => 'bg-violet-600 text-white',  'inactive' => 'bg-white border border-violet-200 text-violet-600 hover:bg-slate-50'],
                    'Bakery_Jadi'       => ['label' => 'Cake & Pastry', 'active' => 'bg-amber-500 text-white',   'inactive' => 'bg-white border border-amber-200 text-amber-600 hover:bg-slate-50'],
                    'Bakery_Bahan_Baku' => ['label' => 'Bahan Baku',    'active' => 'bg-orange-500 text-white',  'inactive' => 'bg-white border border-orange-200 text-orange-600 hover:bg-slate-50'],
                    'Minuman'           => ['label' => 'Minuman',       'active' => 'bg-cyan-500 text-white',    'inactive' => 'bg-white border border-cyan-200 text-cyan-600 hover:bg-slate-50'],
                    'Snack'             => ['label' => 'Snack',         'active' => 'bg-lime-500 text-white',    'inactive' => 'bg-white border border-lime-200 text-lime-600 hover:bg-slate-50'],
                    'Kemasan'           => ['label' => 'Kemasan',       'active' => 'bg-fuchsia-500 text-white', 'inactive' => 'bg-white border border-fuchsia-200 text-fuchsia-600 hover:bg-slate-50'],
                ]);
        $activeCategory = $category ?? 'all';
    @endphp
    <div class="flex gap-2 flex-wrap">
        @foreach($tabs as $key => $tab)
        <a href="{{ route('items.index', array_merge(request()->query(), ['category' => $key])) }}"
            class="text-sm font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1.5
                {{ $activeCategory === $key ? $tab['active'] : $tab['inactive'] }}">
            {{ $tab['label'] }}
        </a>
        @endforeach
    </div>

    {{-- Product Tables --}}
    @php
        $displayItems = $activeCategory === 'all'
            ? $items->groupBy('category')
            : $items->where('category', $activeCategory)->groupBy('category');
    @endphp

    @foreach($displayItems as $cat => $group)
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <span class="text-xl">{{ match($cat) { 'ATK'=>'📎','Elektronik'=>'🔌','Bakery_Jadi'=>'🍰','Bakery_Bahan_Baku'=>'🧴','Minuman'=>'🥤','Snack'=>'🍿','Kemasan'=>'📦',default=>'📦' } }}</span>
            <div>
                <h3 class="text-sm font-black text-slate-800">
                    {{ match($cat) { 'ATK'=>'ATK (Alat Tulis Kantor)','Elektronik'=>'Elektronik','Bakery_Jadi'=>'Cake & Pastry','Bakery_Bahan_Baku'=>'Bahan Baku','Minuman'=>'Minuman','Snack'=>'Snack','Kemasan'=>'Kemasan (Mika, Dus, dll)',default=>$cat } }}
                </h3>
                <p class="text-xs text-slate-400">{{ $group->count() }} produk · {{ number_format($group->sum('stock')) }} unit total</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Nama Produk</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Satuan</th>
                        @can('owner-or-admin')
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Harga Jual</th>
                        @endcan
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide text-center">Stok</th>
                        @can('admin-only')
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide text-center">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($group as $item)
                    <tr class="table-row {{ $item->stock == 0 ? 'bg-rose-50/40' : ($item->stock <= 5 ? 'bg-amber-50/30' : '') }}">
                        <td class="px-5 py-3.5">
                            <div class="text-sm font-bold text-slate-800">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">{{ Str::limit($item->description, 55) }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-slate-500">{{ $item->unit }}</td>
                        @can('owner-or-admin')
                        <td class="px-5 py-3.5 font-mono text-sm font-bold text-slate-700">
                            Rp{{ number_format($item->price, 0, ',', '.') }}
                        </td>
                        @endcan
                        <td class="px-5 py-3.5 text-center">
                            @if($item->stock == 0)
                                <span class="badge bg-rose-50 text-rose-600 border border-rose-200 font-black">HABIS</span>
                            @elseif($item->stock <= 5)
                                <span class="text-sm font-black text-amber-500">⚠ {{ $item->stock }}</span>
                                <span class="text-xs text-slate-400 block">menipis</span>
                            @else
                                <span class="text-sm font-black text-slate-700">{{ $item->stock }}</span>
                            @endif
                        </td>
                        @can('admin-only')
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('items.edit', $item) }}"
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-all">Edit</a>
                                <form id="delete-form-{{ $item->id }}" action="{{ route('items.destroy', $item) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button"
                                    onclick="openDeleteModal('delete-form-{{ $item->id }}', {{ Js::from($item->name) }})"
                                    class="text-xs font-bold text-rose-500 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-all cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if($displayItems->isEmpty())
    <div class="card p-14 text-center">
        <div class="text-5xl mb-4">📦</div>
        <p class="text-base font-bold text-slate-500">Belum ada produk terdaftar.</p>
        <p class="text-sm text-slate-400 mt-1">Mulai dengan menambahkan produk pertama.</p>
        @can('admin-only')
        <a href="{{ route('items.create') }}" class="btn-primary mt-4 inline-flex">+ Tambah Produk Baru</a>
        @endcan
    </div>
    @endif

    {{-- Delete Modal — admin only --}}
    @can('admin-only')
    <div id="deleteModal" class="app-modal fixed inset-0 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4" style="z-index:9999;">
        <div class="modal-card outline-none bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4"
             role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" tabindex="-1">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-rose-50 flex items-center justify-center text-2xl shrink-0">🗑️</div>
                <div>
                    <h3 id="deleteModalTitle" class="text-base font-black text-slate-800">Hapus Produk?</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            <p class="text-sm text-slate-600">
                Anda akan menghapus <span id="deleteItemName" class="font-bold text-slate-800"></span> dari katalog.
            </p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('deleteModal')" class="btn-ghost flex-1 justify-center">Batal</button>
                <button type="button" onclick="confirmDelete()"
                    class="bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl px-4 py-2.5 flex-1 transition-all cursor-pointer">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <script>
    let pendingDeleteFormId = null;

    (function relocateModals() {
        document.querySelectorAll('.app-modal').forEach(function (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });
    })();

    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { modal.classList.add('modal-show'); });
        });
        const panel = modal.querySelector('.modal-card');
        if (panel) panel.focus();
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('modal-show');
        document.body.classList.remove('overflow-hidden');
        window.setTimeout(function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 180);
        pendingDeleteFormId = null;
    }

    function openDeleteModal(formId, itemName) {
        pendingDeleteFormId = formId;
        document.getElementById('deleteItemName').textContent = itemName;
        openModal('deleteModal');
    }

    function confirmDelete() {
        if (pendingDeleteFormId) {
            document.getElementById(pendingDeleteFormId).submit();
        }
    }

    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal('deleteModal');
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal('deleteModal');
    });
    </script>
    @endcan

</div>
@endsection