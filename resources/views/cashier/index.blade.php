@extends('layouts.app')

@section('title', 'Kasir / POS')
@section('breadcrumb', 'Point of Sale')

@section('content')
<div class="grid grid-cols-5 gap-5 min-w-5xl" x-data="kasirApp()">
    {{-- ══ KIRI: Katalog Produk ══ --}}
    <div class="lg:col-span-3 space-y-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Kasir Stok Rajawali</h2>
            <p class="text-sm text-slate-400 mt-0.5">Klik produk untuk menambahkan ke keranjang. Bahan baku tidak tersedia di POS.</p>
        </div>

        {{-- Pencarian --}}
        <div class="relative">
            <input type="text" x-model="search" placeholder="Cari nama produk..." class="form-input pl-10 shadow-sm">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
        </div>

        {{-- Tab Kategori --}}
        <div class="flex gap-2 flex-wrap">
            <button @click="activeTab = 'all'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'all' ? 'bg-slate-800 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50'">
                Semua
            </button>
            <button @click="activeTab = 'ATK'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'ATK' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-blue-200 text-blue-600 hover:bg-slate-50'">
                ATK
            </button>
            <button @click="activeTab = 'Elektronik'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'Elektronik' ? 'bg-violet-600 text-white shadow-sm' : 'bg-white border border-violet-200 text-violet-600 hover:bg-slate-50'">
                Elektronik
            </button>
            <button @click="activeTab = 'Bakery_Jadi'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'Bakery_Jadi' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white border border-amber-200 text-amber-600 hover:bg-slate-50'">
                Cake & Pastry
            </button>
            <button @click="activeTab = 'Snack'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'Snack' ? 'bg-lime-600 text-white shadow-sm' : 'bg-white border border-lime-200 text-lime-600 hover:bg-slate-50'">
                Snack
            </button>
            <button @click="activeTab = 'Minuman'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'Minuman' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white border border-sky-200 text-sky-600 hover:bg-slate-50'">
                Minuman
            </button>
            <button @click="activeTab = 'Kemasan'" type="button" class="text-sm font-bold px-4 py-2 rounded-xl transition-all" :class="activeTab === 'Kemasan' ? 'bg-stone-600 text-white shadow-sm' : 'bg-white border border-stone-200 text-stone-600 hover:bg-slate-50'">
                Kemasan
            </button>
        </div>

        {{-- Grid Produk --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($items as $item)
            <div x-show="(activeTab === 'all' || activeTab === '{{ $item->category }}') && (search === '' || '{{ strtolower($item->name) }}'.includes(search.toLowerCase()))"
                @click="{{ $item->stock > 0 ? "addToCart({$item->id}, '".addslashes($item->name)."', {$item->price}, {$item->stock}, '".addslashes($item->unit)."')" : '' }}"
                class="card group select-none transition-all duration-150 {{ $item->stock > 0 ? 'cursor-pointer hover:border-amber-400 hover:shadow-md active:scale-97' : 'opacity-50 cursor-not-allowed bg-slate-50' }}" style="padding:16px">
                <div class="flex items-start justify-between mb-3">
                    <span class="badge {{ $item->category_color }} border text-[10px]">
                        {{ $item->category_emoji }} {{ $item->category_label }}
                    </span>
                    @if($item->stock == 0)
                    <span class="badge bg-rose-50 text-rose-500 border border-rose-200 text-[10px]">HABIS</span>
                    @elseif($item->stock <= 5)
                    <span class="badge bg-amber-50 text-amber-500 border border-amber-100 text-[10px]">⚠ {{ $item->stock }}</span>
                    @endif
                </div>
                <div class="font-bold text-sm text-slate-800 leading-snug group-hover:text-amber-700 line-clamp-2 min-h-10 transition-colors">
                    {{ $item->name }}
                </div>
                <div class="mt-3 flex items-end justify-between">
                    <span class="font-black text-sm text-slate-800">Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                    <span class="text-xs text-slate-400">{{ $item->stock }} {{ $item->unit }}</span>
                </div>
                @if($item->stock > 0)
                <div class="mt-2 w-full text-center text-[10px] font-bold text-slate-300 group-hover:text-amber-500 transition-colors">+ Klik untuk tambah</div>
                @endif
            </div>
            @endforeach
        </div>

        @if($items->isEmpty())
        <div class="card p-14 text-center">
            <div class="text-4xl mb-3">📦</div>
            <p class="text-sm font-bold text-slate-400">Belum ada produk tersedia.</p>
        </div>
        @endif
    </div>

    {{-- ══ KANAN: Keranjang & Pembayaran ══ --}}
    <div class="lg:col-span-2">
        <div class="card sticky top-20 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-linear-to-r from-amber-50 to-orange-50">
                <div>
                    <h3 class="text-base font-black text-amber-700">Keranjang Belanja</h3>
                    <p class="text-xs text-amber-600 mt-0.5" x-text="cart.length + ' item dipilih'"></p>
                </div>
                <button @click="clearCart()" type="button" x-show="cart.length > 0" class="text-xs font-bold text-rose-500 hover:text-rose-700 bg-rose-50 border border-rose-100 px-3 py-1.5 rounded-lg transition-all">
                    Kosongkan
                </button>
            </div>

            <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                <template x-if="cart.length === 0">
                    <div class="p-10 text-center text-slate-300">
                        <div class="text-5xl mb-3">🛒</div>
                        <p class="text-sm font-bold">Klik produk di kiri untuk menambahkan</p>
                    </div>
                </template>
                <template x-for="item in cart" :key="item.id">
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-slate-800 truncate" x-text="item.name"></div>
                            <div class="text-xs text-slate-400 font-mono" x-text="'Rp' + formatNum(item.price) + ' / ' + item.unit"></div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                           <button @click="decreaseQty(item.id)" type="button" class="w-10 h-10 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-700 font-black flex items-center justify-center transition-all text-base leading-none">−</button>
                            <span class="w-8 text-center font-black text-sm text-slate-800" x-text="item.qty"></span>
                            <button @click="increaseQty(item.id)" type="button" class="w-10 h-10 rounded-lg bg-slate-100 hover:bg-emerald-100 text-slate-700 font-black flex items-center justify-center transition-all text-base leading-none">+</button>
                        </div>
                        <div class="text-sm font-black text-slate-800 w-20 text-right shrink-0" x-text="'Rp' + formatNum(item.price * item.qty)"></div>
                        <button @click="removeFromCart(item.id)" type="button" class="text-slate-300 hover:text-rose-500 transition-all shrink-0 ml-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="px-5 py-4 space-y-4 border-t border-slate-100 bg-slate-50/50">
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500">Total Belanja</span>
                    <span class="font-black text-xl text-slate-800" x-text="'Rp' + formatNum(total)"></span>
                </div>

                <div>
                    <label class="form-label">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="paymentMethod = 'cash'" type="button" class="text-sm font-bold py-3 rounded-xl border transition-all" :class="paymentMethod === 'cash' ? 'bg-emerald-500 border-emerald-500 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:border-emerald-300'">
                            Tunai
                        </button>
                        <button @click="paymentMethod = 'qris'" type="button" class="text-sm font-bold py-3 rounded-xl border transition-all" :class="paymentMethod === 'qris' ? 'bg-violet-600 border-violet-600 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:border-violet-300'">
                            QRIS
                        </button>
                        <button @click="paymentMethod = 'transfer'" type="button" class="text-sm font-bold py-3 rounded-xl border transition-all" :class="paymentMethod === 'transfer' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:border-blue-300'">
                            Transfer
                        </button>
                    </div>
                </div>

                <div x-show="paymentMethod === 'cash'" x-transition>
                    <label class="form-label">Uang yang Diterima</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" x-model.number="paid" :min="total" placeholder="0"
    @keydown.enter="cart.length && !(paymentMethod==='cash' && paid<total) && !processing && processCheckout()"
    class="form-input pl-10 font-mono font-bold text-right text-base">
                    </div>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center justify-between" x-show="paymentMethod === 'cash'" x-transition>
                    <span class="text-sm font-bold text-emerald-700">💰 Kembalian</span>
                    <span class="font-black text-lg" :class="change >= 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="'Rp' + formatNum(Math.max(0, change))"></span>
                </div>

                <div class="bg-violet-50 border border-violet-200 rounded-xl px-4 py-3 text-sm text-violet-700 font-semibold" x-show="paymentMethod === 'qris'" x-transition>
                    📱 Tampilkan QR Code QRIS kepada pelanggan
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-700 font-semibold" x-show="paymentMethod === 'transfer'" x-transition>
                    🏦 Konfirmasi transfer ke rekening toko terlebih dahulu
                </div>

                <button @click="processCheckout()" type="button" :disabled="cart.length === 0 || (paymentMethod === 'cash' && paid < total) || processing"
                    class="w-full font-black text-base py-4 rounded-xl active:scale-98 transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-sm"
                    :style="(cart.length > 0 && !(paymentMethod === 'cash' && paid < total) && !processing) ? 'background:#d97706; color:#fff;' : 'background:#94a3b8; color:#fff;'">
                    <span x-show="!processing">Proses Transaksi</span>
                    <span x-show="processing">⏳ Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MOBILE: Floating Cart Bar (hanya muncul di bawah xl, saat keranjang tidak kosong) ══ --}}
<div x-show="cart.length > 0"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="lg:hidden fixed bottom-4 left-4 right-4 z-50"
     x-cloak>
    <div class="bg-amber-500 text-white rounded-2xl shadow-xl shadow-amber-500/30 px-5 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center font-black text-sm" x-text="cart.length"></div>
            <div>
                <div class="text-xs font-bold opacity-80">Item di keranjang</div>
                <div class="font-black text-base" x-text="'Rp' + formatNum(total)"></div>
            </div>
        </div>
        <button @click="$el.closest('[x-data]').querySelector('.lg\\:col-span-2').scrollIntoView({behavior:'smooth'})"
                type="button"
                class="bg-white text-amber-600 font-black text-sm px-4 py-2 rounded-xl transition-all active:scale-95">
            Lihat Keranjang ↓
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
function kasirApp() {
    return {
        cart: [],
        search: '',
        activeTab: 'all',
        paid: 0,
        processing: false,
        paymentMethod: 'cash',

        get total() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        get change() { return this.paid - this.total; },
        formatNum(n) { return Number(n).toLocaleString('id-ID'); },

        addToCart(id, name, price, stock, unit) {
            if (stock === 0) return;
            const existing = this.cart.find(i => i.id === id);
            if (existing) {
                if (existing.qty < stock) existing.qty++;
                else Swal.fire({ icon: 'warning', title: 'Stok terbatas!', text: `Hanya tersedia ${stock} ${unit}.`, confirmButtonColor: '#f59e0b', timer: 2000, showConfirmButton: false });
            } else {
                this.cart.push({ id, name, price, stock, unit, qty: 1 });
            }
        },

        increaseQty(id) {
            const item = this.cart.find(i => i.id === id);
            if (item && item.qty < item.stock) item.qty++;
            else if (item) Swal.fire({ icon: 'warning', text: 'Stok tidak mencukupi!', timer: 1500, showConfirmButton: false });
        },

        decreaseQty(id) {
            const item = this.cart.find(i => i.id === id);
            if (item) {
                if (item.qty > 1) item.qty--;
                else this.removeFromCart(id);
            }
        },

        removeFromCart(id) { this.cart = this.cart.filter(i => i.id !== id); },
        clearCart() {
            Swal.fire({
                title: 'Kosongkan keranjang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, kosongkan',
                cancelButtonText: 'Batal',
            }).then(r => { if (r.isConfirmed) { this.cart = []; this.paid = 0; } });
        },

        async cetakNotaPdf(transId) {
            try {
                const res = await fetch(`/kasir/nota/${transId}`, { headers: { 'Accept': 'application/json' } });
                const nota = await res.json();

                console.log("Data Nota:", nota);

                const { jsPDF } = window.jspdf;
                const itemHeight = (nota.items.length * 8) + 120;
                const doc = new jsPDF({ unit: 'mm', format: [80, itemHeight], orientation: 'portrait' });

                const pageW = 80, margin = 6;
                let y = 10;

                const writeLine = (txt, size = 8, style = 'normal', align = 'left') => {
                    doc.setFontSize(size); doc.setFont('courier', style);
                    if (align === 'center') doc.text(txt, pageW / 2, y, { align: 'center' });
                    else if (align === 'right') doc.text(txt, pageW - margin, y, { align: 'right' });
                    else doc.text(txt, margin, y);
                    y += (size * 0.45) + 1.5;
                };

                const writeHr = (dash = false) => {
                    doc.setDrawColor(100); doc.setLineWidth(0.2);
                    doc.setLineDashPattern(dash ? [1, 1] : [], 0);
                    doc.line(margin, y - 1, pageW - margin, y - 1);
                    y += 3;
                };

                writeLine('TOKO RAJAWALI', 12, 'bold', 'center');
                writeLine('Computer • Cake & Pastry • ATK', 8, 'normal', 'center');
                writeLine('Tondano, Sulawesi Utara', 8, 'normal', 'center');
                writeHr();

                // ── Pakai daily_no (urutan harian) bukan ID global database ──
                writeLine(`No. Transaksi : #${nota.daily_no}`, 8, 'bold');
                // ── Waktu sudah WITA dari server (format "dd/mm/yyyy HH:ii") ──
                writeLine(`Tanggal       : ${nota.created_at} WITA`, 7.5);
                writeLine(`Operator      : ${nota.operator || 'Kasir'}`, 7.5);

                const pmLabel = { cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer Bank' }[nota.payment_method] || nota.payment_method;
                writeLine(`Metode Bayar  : ${pmLabel}`, 7.5, 'bold');
                writeHr(true);

                writeLine('BARANG', 8, 'bold'); y += 1;

                nota.items.forEach(item => {
                    const nameLine = item.name.length > 25 ? item.name.substring(0, 25) + '...' : item.name;
                    writeLine(nameLine, 8, 'normal');
                    doc.setFontSize(7.5); doc.setFont('courier', 'normal');
                    doc.text(`  ${item.qty} ${item.unit} x Rp${this.formatNum(item.price)}`, margin, y);
                    doc.text(`Rp${this.formatNum(item.subtotal)}`, pageW - margin, y, { align: 'right' });
                    y += 4.5;
                });
                writeHr();

                doc.setFontSize(8.5); doc.setFont('courier', 'bold');
                doc.text('TOTAL', margin, y);
                doc.text(`Rp${this.formatNum(nota.total_amount)}`, pageW - margin, y, { align: 'right' });
                y += 5;

                if (nota.payment_method === 'cash') {
                    doc.setFont('courier', 'normal');
                    doc.text('BAYAR', margin, y);
                    doc.text(`Rp${this.formatNum(nota.paid_amount)}`, pageW - margin, y, { align: 'right' });
                    y += 4.5;
                    doc.setFont('courier', 'bold');
                    doc.text('KEMBALIAN', margin, y);
                    doc.text(`Rp${this.formatNum(nota.change_amount)}`, pageW - margin, y, { align: 'right' });
                    y += 5;
                }
                writeHr(true);

                writeLine('Terima kasih atas kunjungan Anda!', 7.5, 'normal', 'center');
                y += 2;
                writeLine('Powered by Rajawali POS', 7, 'italic', 'center');

                const blob = doc.output('blob');
                const url = URL.createObjectURL(blob);

                await new Promise((resolve) => {
                    const oldModal = document.getElementById('print-preview-modal');
                    if (oldModal) oldModal.remove();

                    const overlay = document.createElement('div');
                    overlay.id = 'print-preview-modal';
                    overlay.style.cssText = `
                        position: fixed; inset: 0; z-index: 99999;
                        background: rgba(15, 23, 42, 0.75);
                        display: flex; align-items: center; justify-content: center;
                        padding: 16px;
                    `;

                    const panel = document.createElement('div');
                    panel.style.cssText = `
                        background: #fff; border-radius: 14px; overflow: hidden;
                        width: 100%; max-width: 420px; height: 92vh;
                        display: flex; flex-direction: column;
                        box-shadow: 0 20px 50px rgba(0,0,0,0.35);
                    `;

                    const header = document.createElement('div');
                    header.style.cssText = `
                        display: flex; align-items: center; justify-content: space-between;
                        padding: 12px 16px; background: #fffbeb; border-bottom: 1px solid #fde68a;
                    `;
                    header.innerHTML = `<span style="font-weight:800; font-size:14px; color:#92400e;">🖨 Preview Struk</span>`;

                    const printBtn = document.createElement('button');
                    printBtn.type = 'button';
                    printBtn.textContent = '🖨 Print';
                    printBtn.style.cssText = `
                        font-weight: 800; font-size: 13px; color: #fff;
                        background: #16a34a; border: none; border-radius: 8px;
                        padding: 7px 14px; cursor: pointer; margin-right: 8px;
                    `;

                    const closeBtn = document.createElement('button');
                    closeBtn.type = 'button';
                    closeBtn.textContent = 'Tutup';
                    closeBtn.style.cssText = `
                        font-weight: 800; font-size: 13px; color: #fff;
                        background: #d97706; border: none; border-radius: 8px;
                        padding: 7px 16px; cursor: pointer;
                    `;

                    const btnWrap = document.createElement('div');
                    btnWrap.style.cssText = 'display:flex; align-items:center;';
                    btnWrap.appendChild(printBtn);
                    btnWrap.appendChild(closeBtn);
                    header.appendChild(btnWrap);

                    const iframe = document.createElement('iframe');
                    iframe.style.cssText = 'flex: 1; width: 100%; border: 0;';

                    const footer = document.createElement('div');
                    footer.style.cssText = `
                        padding: 10px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0;
                        font-size: 12px; color: #64748b; text-align: center;
                    `;
                    footer.textContent = 'Klik "Print" untuk mencetak struk, lalu "Tutup" setelah selesai.';

                    panel.appendChild(header);
                    panel.appendChild(iframe);
                    panel.appendChild(footer);
                    overlay.appendChild(panel);
                    document.body.appendChild(overlay);

                    let finished = false;
                    const finish = () => {
                        if (finished) return;
                        finished = true;
                        overlay.remove();
                        URL.revokeObjectURL(url);
                        resolve();
                    };

                    // ── Print HANYA dipicu manual saat tombol diklik, TIDAK ada auto-trigger onload ──
                    printBtn.addEventListener('click', () => {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch (e) {
                            console.warn('Print manual diperlukan:', e);
                        }
                    });

                    closeBtn.addEventListener('click', finish);
                    overlay.addEventListener('click', (e) => { if (e.target === overlay) finish(); });

                    iframe.src = url;
                });

            } catch (e) {
                console.error(e);
                Swal.fire({ icon: 'error', title: 'Gagal cetak nota', text: 'Tidak dapat memuat atau mencetak data transaksi.', confirmButtonColor: '#f59e0b' });
            }
        },

        async processCheckout() {
            if (this.cart.length === 0) return;
            if (this.paymentMethod === 'cash' && this.paid < this.total) {
                Swal.fire({ icon: 'warning', title: 'Nominal kurang!', text: 'Nominal bayar harus lebih besar atau sama dengan total.', confirmButtonColor: '#f59e0b' });
                return;
            }
            this.processing = true;

            const payload = {
                cart: this.cart.map(i => ({ id: i.id, quantity: i.qty })),
                received_amount: this.paymentMethod === 'cash' ? this.paid : this.total,
                total_amount: this.total,
                payment_method: this.paymentMethod,
            };

            try {
                const res = await fetch('{{ route("kasir.checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    const transId = data.transaction_id;
                    const self = this;

                    Swal.fire({
                        icon: 'success',
                        title: 'Transaksi Berhasil!',
                        text: 'Total: Rp' + this.formatNum(this.total),
                        showCancelButton: true,
                        confirmButtonColor: '#d97706',
                        confirmButtonText: '🖨 Cetak Struk Nota',
                        cancelButtonText: 'Transaksi Baru',
                        cancelButtonColor: '#64748b',
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            await self.cetakNotaPdf(transId);
                            this.cart = [];
                            this.paid = 0;
                            location.reload();
                        } else {
                            this.cart = [];
                            this.paid = 0;
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#4f46e5' });
                }
            } catch (e) {
                console.error(e);
                Swal.fire({ icon: 'error', title: 'Error Koneksi', text: 'Gagal terhubung ke server.', confirmButtonColor: '#4f46e5' });
            } finally {
                this.processing = false;
            }
        }
    }
}
</script>
@endpush