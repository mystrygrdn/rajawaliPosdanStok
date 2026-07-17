@extends('layouts.app')

@section('title', $isDapur ? 'Catat Bahan Masuk' : 'Catat Barang Masuk')
@section('breadcrumb', $isDapur ? 'Bahan Masuk / Catat' : 'Barang Masuk / Catat')

@section('content')
<div class="max-w-xl mx-auto space-y-5"
     x-data="{
        confirmOpen: false,
        summary: { produk: '-', jumlah: '-', tanggal: '-', supplier: '-' },
        openConfirm() {
            const form = document.getElementById('inboundForm');
            if (!form.reportValidity()) return;
            const itemSelect = document.getElementById('item_id');
            const selectedOpt = itemSelect.options[itemSelect.selectedIndex];
            const produkText = selectedOpt && selectedOpt.value ? selectedOpt.text.split(' — ')[0] : '-';
            const qty = document.getElementById('quantity').value || 0;
            const unit = document.getElementById('unit-label').textContent || 'pcs';
            const tanggal = document.getElementById('date').value || '-';
            const supplier = document.getElementById('supplier').value.trim() || '-';
            this.summary = { produk: produkText, jumlah: qty + ' ' + unit, tanggal: tanggal, supplier: supplier };
            this.confirmOpen = true;
        }
     }">

    <a href="{{ route('inbounds.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-slate-700 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Riwayat
    </a>

    @if($isDapur)
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-emerald-700 font-medium">
            Form ini khusus untuk mencatat <strong>penerimaan bahan baku dapur</strong>. Stok akan otomatis bertambah.
        </p>
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-700 font-medium">
            Kuantitas akan otomatis ditambahkan ke stok produk.
        </p>
    </div>
    @endif

    <div class="card p-6 space-y-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-800">
                    {{ $isDapur ? 'Catat Bahan Baku Masuk' : 'Pencatatan Barang Masuk' }}
                </h2>
                <p class="text-sm text-slate-400 mt-0.5">
                    {{ $isDapur ? 'Stok bahan baku akan otomatis bertambah.' : 'Kuantitas akan otomatis ditambahkan ke stok produk.' }}
                </p>
            </div>
        </div>

        <form id="inboundForm" action="{{ route('inbounds.store') }}" method="POST" class="space-y-5"
              @submit.prevent="openConfirm()">
            @csrf

            <div>
                <label for="item_id" class="form-label">
                    {{ $isDapur ? 'Pilih Bahan Baku' : 'Pilih Produk' }} <span class="text-rose-500">*</span>
                </label>
                <select name="item_id" id="item_id" required
                    class="form-select {{ $errors->has('item_id') ? 'error' : '' }}"
                    onchange="updateUnit(this)">
                    <option value="">-- {{ $isDapur ? 'Pilih Bahan Baku' : 'Pilih Produk' }} --</option>
                    @foreach($items->groupBy('category') as $cat => $group)
                    <optgroup label="{{ match($cat) {
    'ATK'               => '📎 ATK',
    'Elektronik'        => '🔌 Elektronik',
    'Bakery_Jadi'       => '🍰 Cake & Pastry',
    'Bakery_Bahan_Baku' => '🧴 Bahan Baku',
    'Minuman'           => '🥤 Minuman',
    'Snack'             => '🍿 Snack',
    'Kemasan'           => '📦 Kemasan',
    default             => $cat,
} }}">
                        @foreach($group as $item)
                        <option value="{{ $item->id }}" data-unit="{{ $item->unit }}"
                            {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} — Stok: {{ $item->stock }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('item_id')
                <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="form-label">Jumlah Masuk <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" required
                            class="form-input pr-16 font-mono {{ $errors->has('quantity') ? 'error' : '' }}">
                        <span id="unit-label" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">pcs</span>
                    </div>
                    @error('quantity')
                    <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date" class="form-label">Tanggal Masuk <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="form-input">
                </div>
            </div>

            <div>
                <label for="supplier" class="form-label">
                    {{ $isDapur ? 'Sumber Bahan' : 'Nama Supplier / Vendor' }}
                    <span class="text-slate-300">(Opsional)</span>
                </label>
                <input type="text" name="supplier" id="supplier" value="{{ old('supplier') }}"
                    placeholder="{{ $isDapur ? 'Contoh: PT. Rajawali, Pasar, dll' : 'Contoh: PT. Maju Jaya Supplier' }}"
                    class="form-input {{ $errors->has('supplier') ? 'error' : '' }}">
                @error('supplier')
                <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="form-label">Catatan <span class="text-slate-300">(Opsional)</span></label>
                <textarea name="notes" id="notes" rows="2"
                    placeholder="{{ $isDapur ? 'Kondisi bahan, catatan khusus, dll...' : 'No. PO, nama kurir, nomor surat jalan, dll...' }}"
                    class="form-input resize-none">{{ old('notes') }}</textarea>
            </div>

            @error('error')
            <div class="bg-rose-50 border border-rose-200 text-rose-600 text-sm font-semibold px-4 py-3 rounded-xl">
                {{ $message }}
            </div>
            @enderror

            <div class="flex gap-3 pt-2">
                <a href="{{ route('inbounds.index') }}" class="btn-ghost flex-1 justify-center">Batal</a>
                <button type="submit" class="btn-emerald flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan & Tambah Stok
                </button>
            </div>
        </form>
    </div>

    <template x-teleport="body">
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="margin: 0;">
            <div x-show="confirmOpen"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="confirmOpen = false"></div>
            <div x-show="confirmOpen"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800">Simpan Barang Masuk?</h3>
                        <p class="text-sm text-slate-400 mt-0.5">Pastikan data di bawah ini sudah benar.</p>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">Produk</span>
                        <span class="font-bold text-slate-800 text-right" x-text="summary.produk"></span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">Jumlah Masuk</span>
                        <span class="font-bold text-slate-800" x-text="summary.jumlah"></span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">Tanggal</span>
                        <span class="font-bold text-slate-800" x-text="summary.tanggal"></span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">{{ $isDapur ? 'Sumber Bahan' : 'Supplier' }}</span>
                        <span class="font-bold text-slate-800 text-right" x-text="summary.supplier"></span>
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="confirmOpen = false" class="btn-ghost flex-1 justify-center">Batal, Cek Lagi</button>
                    <button type="button" @click="document.getElementById('inboundForm').submit()" class="btn-emerald flex-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Ya, Simpan
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function updateUnit(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('unit-label').textContent = opt.dataset.unit || 'pcs';
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('item_id');
    if (sel) updateUnit(sel);
});
</script>
@endpush
@endsection