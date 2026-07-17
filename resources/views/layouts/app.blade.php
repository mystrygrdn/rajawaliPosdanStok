<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <title>@yield('title', 'Rajawali') — POS & Stok</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    /* ── Style struktural halaman saja. Semua class desain (form-input, card,
         btn-*, badge, nav-link, dst) sudah dipindah ke resources/css/app.css
         di dalam @layer components, supaya utility class Tailwind (pl-10,
         lg:col-span-2, dst) bisa benar-benar meng-override-nya. ── */
    </style>

    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile overlay -->
    <div id="overlay" :class="sidebarOpen ? 'show' : ''" @click="sidebarOpen = false"></div>

    <div class="flex min-h-screen">

        <!-- ══ SIDEBAR ══ -->
        <aside id="sidebar" :class="sidebarOpen ? 'open' : ''"
            class="w-64 bg-white border-r border-slate-100 flex flex-col shrink-0 h-screen sticky top-0 shadow-sm"
            style="z-index:50">

            <!-- Brand (tinggi disamakan dgn header via .brand-row, py-4 ↔ header py-4) -->
            <div class="brand-row px-4 py-4 border-b border-slate-100 flex items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center shadow-lg shadow-indigo-500/30 shrink-0">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21 3L3 10.53v.98l6.84 2.65L12.48 21h.98L21 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-black text-slate-900 text-sm leading-none">Toko Rajawali Tondano</div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-0.5">POS Kasir & Stok</div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">

    <div class="section-title">Menu Utama</div>

    {{-- Dashboard — semua role --}}
    <a href="{{ route('dashboard') }}"
        class="nav-link {{ request()->routeIs('dashboard') ? 'active-indigo' : '' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard
    </a>

    {{-- Katalog — semua role, label berbeda untuk dapur --}}
    <a href="{{ route('items.index') }}"
        class="nav-link {{ request()->routeIs('items.*') ? 'active-indigo' : '' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        {{-- Dapur lihat "Produk Bakery", role lain lihat "Katalog Produk" --}}
        {{ auth()->user()->isDapur() ? 'Produk Bakery' : 'Katalog Produk' }}
    </a>

    <div class="section-title">Transaksi Stok</div>

    {{-- Barang Masuk — semua role bisa lihat, label berbeda untuk dapur --}}
    <a href="{{ route('inbounds.index') }}"
        class="nav-link {{ request()->routeIs('inbounds.*') ? 'active-emerald' : '' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
        {{ auth()->user()->isDapur() ? 'Bahan Masuk' : 'Barang Masuk' }}
        <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-md
            {{ request()->routeIs('inbounds.*') ? 'bg-white/25 text-white' : 'bg-emerald-50 text-emerald-600' }}">
            IN
        </span>
    </a>

    {{-- Barang Keluar — semua role bisa lihat, label berbeda untuk dapur --}}
    <a href="{{ route('outbounds.index') }}"
        class="nav-link {{ request()->routeIs('outbounds.*') ? 'active-rose' : '' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        {{ auth()->user()->isDapur() ? 'Bahan Keluar' : 'Barang Keluar' }}
        <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-md
            {{ request()->routeIs('outbounds.*') ? 'bg-white/25 text-white' : 'bg-rose-50 text-rose-500' }}">
            OUT
        </span>
    </a>

    {{-- Kasir / POS — admin saja --}}
    @can('admin-only')
    <div class="section-title">Point of Sale</div>

    <a href="{{ route('kasir.index') }}"
        class="nav-link {{ request()->routeIs('kasir.*') ? 'active-amber' : '' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Kasir / POS
    </a>
    @endcan

    {{-- Laporan — admin dan owner saja, dapur tidak tampil --}}
    @can('view-laporan')
    <div class="section-title">Analitik</div>

    <a href="{{ route('laporan.index') }}"
        class="nav-link {{ request()->routeIs('laporan.*') ? 'active-violet' : '' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Laporan & Analitik
    </a>
    @endcan

</nav>

            <!-- Logout -->
            <div class="px-3 py-3 border-t border-slate-100" x-data="{ confirmLogout: false }">
                <button type="button" @click="confirmLogout = true" class="nav-link w-full text-rose-500 hover:bg-rose-50 hover:text-rose-600" style="justify-content:flex-start">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar dari Sistem
                </button>

                <!-- Modal Konfirmasi Logout -->
                <template x-teleport="body">
                    <div x-show="confirmLogout"
                         x-cloak
                         class="fixed inset-0 flex items-center justify-center px-4"
                         style="z-index: 9999;"
                         @keydown.escape.window="confirmLogout = false">

                        <!-- Backdrop -->
                        <div x-show="confirmLogout"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                             @click="confirmLogout = false"></div>

                        <!-- Modal Card -->
                        <div x-show="confirmLogout"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="relative card w-full max-w-sm p-6 shadow-2xl"
                             role="alertdialog"
                             aria-modal="true"
                             aria-labelledby="logout-modal-title"
                             @click.outside="confirmLogout = false">

                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-rose-50 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 id="logout-modal-title" class="font-black text-slate-900 text-sm">Keluar dari Sistem?</h3>
                                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                        Sesi Anda akan diakhiri dan Anda perlu login kembali untuk mengakses sistem POS &amp; Stok Rajawali.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 mt-6">
                                <button type="button" @click="confirmLogout = false" class="btn-ghost flex-1 justify-center">
                                    Batal
                                </button>
                                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        class="w-full justify-center inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-colors">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Ya, Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </aside>

        <!-- ══ MAIN ══ -->
        <main class="flex-1 flex flex-col min-w-0">

            <!-- Top Bar (tinggi dikunci 72px via .topbar-row, sejajar dgn .brand-row sidebar) -->
            <header class="topbar-row bg-white border-b border-slate-100 px-5 py-4 flex items-center justify-between shrink-0 sticky top-0 z-40">

                <!-- Kiri: Hamburger + Breadcrumb -->
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Mobile hamburger -->
                    <button id="mobile-menu" @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-all shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Breadcrumb -->
                    <div class="flex items-center gap-2 text-sm min-w-0">
                        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-indigo-600 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </a>
                        <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="font-black text-slate-800 truncate">@yield('breadcrumb', 'Dashboard')</span>
                    </div>
                </div>

                <!-- Kanan: Jam + Profile -->
                <div class="flex items-center gap-3 shrink-0">

                    <!-- 🕐 Live Clock (dipindah dari sidebar ke sini) -->
                    <div class="hidden md:flex flex-col items-end leading-none">
                        <span id="header-date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wide"></span>
                        <span id="header-time" class="text-sm font-black text-slate-700 tabular-nums mt-0.5"></span>
    </div>
                    <div class="w-px h-8 bg-slate-200"></div>

                    <!-- 👤 Info Profil (statis, satu-satunya tempat nama & role ditampilkan) -->
                    <div class="flex items-center gap-2.5 pl-1 pr-2.5 py-1">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-black text-xs shrink-0 shadow-md shadow-indigo-500/20">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-xs font-bold text-slate-700 leading-tight">{{ auth()->user()->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 font-semibold leading-tight">{{ auth()->user()->role_label ?? auth()->user()->role ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-5 md:p-6 overflow-y-auto">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- SweetAlert2 Flash Messages -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live clock — sekarang tampil di header kanan atas
        function updateClock() {
            const now = new Date();
            const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const dateEl = document.getElementById('header-date');
            const timeEl = document.getElementById('header-time');
            if (dateEl) dateEl.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
            if (timeEl) {
                const h = String(now.getHours()).padStart(2,'0');
                const m = String(now.getMinutes()).padStart(2,'0');
                const s = String(now.getSeconds()).padStart(2,'0');
                timeEl.textContent = h + ':' + m + ':' + s;
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        @if(session('success'))
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: @json(session('success')),
            showConfirmButton: false, timer: 4000, timerProgressBar: true,
            customClass: { popup: 'text-sm font-semibold' }
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error', title: 'Terjadi Kesalahan', text: @json(session('error')),
            confirmButtonColor: '#4f46e5', confirmButtonText: 'Mengerti',
            customClass: { popup: 'text-sm' }
        });
        @endif

        @if($errors->has('error'))
        Swal.fire({
            icon: 'error', title: 'Gagal Memproses', text: @json($errors->first('error')),
            confirmButtonColor: '#4f46e5', confirmButtonText: 'Mengerti',
        });
        @endif

        @if($errors->has('cart'))
        Swal.fire({
            icon: 'error', title: 'Error Keranjang', text: @json($errors->first('cart')),
            confirmButtonColor: '#4f46e5', confirmButtonText: 'Mengerti',
        });
        @endif
    });
    </script>

    @stack('scripts')
</body>
</html>