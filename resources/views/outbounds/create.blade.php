@extends('layouts.app')

@section('title', $isDapur ? 'Catat Pemakaian Bahan' : 'Catat Barang Keluar Manual')
@section('breadcrumb', $isDapur ? 'Bahan Keluar / Catat Pemakaian' : 'Barang Keluar / Catat Manual')

@section('content')
<div class="max-w-xl mx-auto space-y-5"
     x-data="{
        confirmOpen: false,
        summary: { produk: '-', jumlah: '-', tanggal: '-', penerima: '-' },
        openConfirm() {
            const form = document.getElementById('outboundForm');
            if (!form.reportValidity()) return;
            const itemSelect = document.getElementById('item_id');
            const selectedOpt = itemSelect.options[itemSelect.selectedIndex];
            const produkText = selectedOpt && selectedOpt.value ? selectedOpt.text.split(' — ')[0] : '-';
            const qty = document.getElementById('quantity').value || 0;
            const unit = document.getElementById('unit-label').textContent || 'pcs';
            const tanggal = document.getElementById('date').value || '-';
            const customer = document.getElementById('customer').value.trim() || '-';
            this.summary = { produk: produkText, jumlah: qty + ' ' + unit, tanggal: tanggal, penerima: customer };
            this.confirmOpen = true;
        }
     }">

    <a href="{{ route('outbounds.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-slate-700 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Riwayat
    </a>

    @if($isDapur)
    <div class="bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-4 h-4 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-orange-700 font-medium">
            Form ini untuk mencatat <strong>bahan baku yang terpakai dalam produksi</strong>. Stok akan otomatis berkurang.
        </p>
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-700 font-medium">
            Form ini untuk mencatat barang keluar <strong>secara manual</strong> (di luar transaksi POS kasir). Stok akan otomatis dikurangi.
        </p>
    </div>
    @endif

    <div class="card p-6 space-y-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 {{ $isDapur ? 'bg-orange-100' : 'bg-rose-100' }} rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 {{ $isDapur ? 'text-orange-600' : 'text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-800">
                    {{ $isDapur ? 'Catat Pemakaian Bahan Baku' : 'Catat Barang Keluar Manual' }}
                </h2>
                <p class="text-sm text-slate-400 mt-0.5">
                    {{ $isDapur ? 'Untuk bahan yang terpakai saat produksi cake & pastry.' : 'Untuk kebutuhan non-kasir: barang rusak, hilang, return, dll.' }}
                </p>
            </div>
        </div>

        <form id="outboundForm" action="{{ route('outbounds.store') }}" method="POST" class="space-y-5"
              @submit.prevent="openConfirm()">
            @csrf

            <div>
                <label for="item_id" class="form-label">
                    {{ $isDapur ? 'Pilih Bahan Baku' : 'Pilih Produk' }} <span class="text-rose-500">*</span>
                </label>
                <select name="item_id" id="item_id" required
                    class="form-select {{ $errors->has('item_id') ? 'error' : '' }}"
                    onchange="showStockInfo(this)">
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
                        <option value="{{ $item->id }}" data-stock="{{ $item->stock }}" data-unit="{{ $item->unit }}"
                            {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} — Stok: {{ $item->stock }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                <p id="stock-info" class="text-xs font-semibold text-emerald-600 mt-1 hidden"></p>
                @error('item_id')
                <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="form-label">
                        {{ $isDapur ? 'Jumlah Terpakai' : 'Jumlah Keluar' }} <span class="text-rose-500">*</span>
                    </label>
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
                    <label for="date" class="form-label">Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="form-input">
                </div>
            </div>

            <div>
                <label for="customer" class="form-label">
                    {{ $isDapur ? 'Keterangan Pemakaian' : 'Penerima / Keterangan' }}
                    <span class="text-slate-300">(Opsional)</span>
                </label>
                <input type="text" name="customer" id="customer" value="{{ old('customer') }}"
                    placeholder="{{ $isDapur ? 'Contoh: untuk produksi donat, croissant, dll' : 'Nama pelanggan, atau alasan (rusak, retur, dll)' }}"
                    class="form-input">
            </div>

            <div>
                <label for="notes" class="form-label">Catatan <span class="text-slate-300">(Opsional)</span></label>
                <textarea name="notes" id="notes" rows="2"
                    placeholder="{{ $isDapur ? 'Detail produksi, batch, catatan khusus...' : 'Detail alasan keluar, nomor referensi, dll...' }}"
                    class="form-input resize-none">{{ old('notes') }}</textarea>
            </div>

            @error('error')
            <div class="bg-rose-50 border border-rose-200 text-rose-600 text-sm font-semibold px-4 py-3 rounded-xl">
                {{ $message }}
            </div>
            @enderror

            <div class="flex gap-3 pt-2">
                <a href="{{ route('outbounds.index') }}" class="btn-ghost flex-1 justify-center">Batal</a>
                <button type="submit" style="background:{{ $isDapur ? '#ea580c' : '#e11d48' }};color:#fff;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;flex:1;justify-content:center;display:flex;align-items:center;gap:6px;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $isDapur ? 'Simpan Pemakaian' : 'Simpan & Kurangi Stok' }}
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
                    <div class="w-11 h-11 {{ $isDapur ? 'bg-orange-100' : 'bg-rose-100' }} rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 {{ $isDapur ? 'text-orange-600' : 'text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800">
                            {{ $isDapur ? 'Simpan Pemakaian Bahan?' : 'Simpan Barang Keluar?' }}
                        </h3>
                        <p class="text-sm text-slate-400 mt-0.5">Pastikan data di bawah ini sudah benar.</p>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">{{ $isDapur ? 'Bahan' : 'Produk' }}</span>
                        <span class="font-bold text-slate-800 text-right" x-text="summary.produk"></span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">{{ $isDapur ? 'Jumlah Terpakai' : 'Jumlah Keluar' }}</span>
                        <span class="font-bold text-slate-800" x-text="summary.jumlah"></span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">Tanggal</span>
                        <span class="font-bold text-slate-800" x-text="summary.tanggal"></span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-400 font-medium">{{ $isDapur ? 'Keterangan' : 'Penerima / Keterangan' }}</span>
                        <span class="font-bold text-slate-800 text-right" x-text="summary.penerima"></span>
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="confirmOpen = false" class="btn-ghost flex-1 justify-center">Batal, Cek Lagi</button>
                    <button type="button" @click="document.getElementById('outboundForm').submit()"
                        style="background:{{ $isDapur ? '#ea580c' : '#e11d48' }};color:#fff;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;flex:1;justify-content:center;display:flex;align-items:center;gap:6px;">
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
function showStockInfo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('stock-info');
    const unitLabel = document.getElementById('unit-label');
    if (opt.value) {
        info.textContent = `Stok tersedia: ${opt.dataset.stock} ${opt.dataset.unit}`;
        info.classList.remove('hidden');
        unitLabel.textContent = opt.dataset.unit || 'pcs';
    } else {
        info.classList.add('hidden');
        unitLabel.textContent = 'pcs';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('item_id');
    if (sel && sel.value) showStockInfo(sel);
});
</script>
@endpush
@endsection