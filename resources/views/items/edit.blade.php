@extends('layouts.app')

@section('title', 'Edit Produk')
@section('breadcrumb', 'Katalog / Edit Produk')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <a href="{{ route('items.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-slate-700 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Katalog
    </a>

    <div class="card p-6 space-y-6">
        <div>
            <h2 class="text-lg font-black text-slate-800">Edit: {{ $item->name }}</h2>
            <p class="text-sm text-slate-400 mt-1">Perubahan akan langsung berlaku di seluruh sistem.</p>
        </div>

        <form action="{{ route('items.update', $item) }}" method="POST" id="editForm" class="space-y-5" onsubmit="return handleFormSubmit(event, 'editModal')">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="form-label">Nama Produk <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}"
                    required class="form-input {{ $errors->has('name') ? 'error' : '' }}">
                @error('name')
                <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category" class="form-label">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category" id="category" required class="form-select {{ $errors->has('category') ? 'error' : '' }}">
                        <option value="ATK" {{ old('category', $item->category) === 'ATK' ? 'selected' : '' }}>📎 ATK</option>
                        <option value="Elektronik" {{ old('category', $item->category) === 'Elektronik' ? 'selected' : '' }}>🔌 Elektronik</option>
                        <option value="Bakery_Jadi" {{ old('category', $item->category) === 'Bakery_Jadi' ? 'selected' : '' }}>🍰 Bakery — Produk Jadi</option>
                        
                        {{-- Menyembunyikan Bahan Baku dari Dropdown edit bagi Admin --}}
                        @if(auth()->user()->role !== 'admin')
                        <option value="Bakery_Bahan_Baku" {{ old('category', $item->category) === 'Bakery_Bahan_Baku' ? 'selected' : '' }}>🧴 Bakery — Bahan Baku</option>
                        @endif
                        
                        <option value="Minuman" {{ old('category', $item->category) === 'Minuman' ? 'selected' : '' }}>🥤 Minuman</option>
                        <option value="Snack" {{ old('category', $item->category) === 'Snack' ? 'selected' : '' }}>🍿 Snack</option>
                        <option value="Kemasan" {{ old('category', $item->category) === 'Kemasan' ? 'selected' : '' }}>📦 Kemasan (Mika, Dus, dll)</option>
                    </select>
                    @error('category')
                    <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="unit" class="form-label">Satuan <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit" id="unit" value="{{ old('unit', $item->unit) }}"
                        required class="form-input {{ $errors->has('unit') ? 'error' : '' }}">
                    @error('unit')
                    <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="price_display" class="form-label">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                    <input type="text" inputmode="numeric" id="price_display"
                        value="{{ number_format((int) old('price', $item->price), 0, ',', '.') }}"
                        placeholder="0" autocomplete="off"
                        oninput="formatPriceInput(this, 'price')"
                        required class="form-input pl-10 font-mono {{ $errors->has('price') ? 'error' : '' }}">
                    <input type="hidden" name="price" id="price" value="{{ old('price', $item->price) }}">
                </div>
                <p class="text-xs text-slate-400 mt-1">Otomatis diformat, contoh: ketik <span class="font-mono">80000</span> akan tampil sebagai <span class="font-mono">80.000</span> (Rp80.000).</p>
                @error('price')
                <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Stok --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="form-label mb-0">Stok Saat Ini</div>
                    <div class="text-xl font-black text-slate-800 mt-1">{{ $item->stock }} <span class="text-sm font-medium text-slate-400">{{ $item->unit }}</span></div>
                </div>
                <div class="text-xs text-slate-400 text-right max-w-30">Ubah stok melalui menu Barang Masuk / Keluar</div>
            </div>

            <div>
                <label for="description" class="form-label">Deskripsi <span class="text-slate-300">(Opsional)</span></label>
                <textarea name="description" id="description" rows="2"
                    class="form-input resize-none">{{ old('description', $item->description) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('items.index') }}" class="btn-ghost flex-1 justify-center">Batal</a>
                <button type="submit" class="btn-primary flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Save Confirmation Modal --}}
    <div id="editModal" class="app-modal fixed inset-0 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4" style="z-index:9999;">
        <div class="modal-card outline-none bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4"
             role="dialog" aria-modal="true" aria-labelledby="editModalTitle" tabindex="-1">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-indigo-50 flex items-center justify-center text-2xl shrink-0">📝</div>
                <div>
                    <h3 id="editModalTitle" class="text-base font-black text-slate-800">Simpan Perubahan?</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perubahan akan langsung berlaku di seluruh sistem.</p>
                </div>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-sm space-y-1.5">
                <div class="flex justify-between gap-3"><span class="text-slate-400">Nama</span><span id="summary-name" class="font-bold text-slate-800 text-right">-</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-400">Satuan</span><span id="summary-unit" class="font-bold text-slate-800 text-right">-</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-400">Harga Jual</span><span id="summary-price" class="font-bold text-slate-800 text-right">Rp0</span></div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editModal')" class="btn-ghost flex-1 justify-center">Batal, Cek Lagi</button>
                <button type="button" onclick="confirmFormSubmit('editForm')" class="btn-primary flex-1 justify-center">Ya, Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    function formatPriceInput(displayEl, hiddenInputId) {
        let raw = displayEl.value.replace(/\D/g, '');
        raw = raw.replace(/^0+(?=\d)/, '');
        document.getElementById(hiddenInputId).value = raw === '' ? 0 : raw;
        displayEl.value = raw === '' ? '' : raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function handleFormSubmit(e, modalId) {
        e.preventDefault();
        document.getElementById('summary-name').textContent = document.getElementById('name').value;
        document.getElementById('summary-unit').textContent = document.getElementById('unit').value;
        
        let rawPrice = document.getElementById('price').value;
        document.getElementById('summary-price').textContent = 'Rp' + parseInt(rawPrice).toLocaleString('id-ID');
        
        openModal(modalId);
        return false;
    }

    function confirmFormSubmit(formId) {
        document.getElementById(formId).submit();
    }

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
    }
</script>
@endsection