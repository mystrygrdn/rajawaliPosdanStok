<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Rajawali POS & Inventory</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md space-y-6">

        {{-- Brand Identity --}}
        <div class="flex flex-col items-center text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                {{-- PERBAIKAN: hapus bg-indigo-650 yang tidak valid, pakai bg-indigo-600 saja --}}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <h1 class="text-base font-black tracking-tight text-slate-900 leading-snug">
                    RAJAWALI POS & INVENTORY
                </h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Cashier and Inventory Management System
                </p>
            </div>
        </div>

        {{-- Login Card --}}
        <div class="bg-white border border-slate-200/80 p-8 rounded-3xl shadow-sm space-y-6">

            <h2 class="text-sm font-black text-slate-800 text-center">
                {{-- PERBAIKAN: hapus typo "text -[80px]" yang ada spasinya --}}
                WELCOME
            </h2>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Username --}}
                <div class="space-y-1">
                    <label for="username"
                        class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">
                        Username
                    </label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        {{-- PERBAIKAN: maxlength sebagai lapisan pertahanan di sisi browser --}}
                        maxlength="100"
                        class="w-full bg-slate-50 border text-xs px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-1 transition-all
                            {{ $errors->has('username') 
                                ? 'border-rose-500 ring-rose-500/10' 
                                : 'border-slate-200 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('username')
                        <span class="text-[10px] font-semibold text-rose-500 mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1">
                    <label for="password"
                        class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">
                        Password
                    </label>
                    <div class="relative flex items-center">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            {{-- PERBAIKAN: autocomplete="current-password" memberi tahu browser
                                 ini adalah field password login, bukan field buat password baru.
                                 Password manager tetap bisa isi otomatis, tapi lebih terkontrol. --}}
                            autocomplete="current-password"
                            maxlength="255"
                            class="w-full bg-slate-50 border border-slate-200 text-xs pl-4 pr-11 py-3 rounded-xl
                                focus:outline-none focus:ring-1 focus:ring-indigo-500 text-slate-800
                                transition-all font-medium
                                {{ $errors->has('password') ? 'border-rose-500' : '' }}">

                        {{-- Tombol toggle show/hide password --}}
                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            aria-label="Tampilkan atau sembunyikan password"
                            class="absolute right-4 text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                    -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-[10px] font-semibold text-rose-500 mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- DIHAPUS: checkbox "Ingat Perangkat Ini" --}}

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3
                        rounded-xl transition-all shadow-md shadow-indigo-600/15 cursor-pointer">
                    {{-- PERBAIKAN: hapus bg-indigo-650 yang tidak valid --}}
                    LOGIN
                </button>

            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-wider">
            © TOKO RAJAWALI COMPUTER, ATK, CAKE & PASTRY TONDANO 2026
        </p>

    </div>

    <script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon       = document.getElementById('eye-icon');
        const isHidden      = passwordInput.type === 'password';

        passwordInput.type = isHidden ? 'text' : 'password';

        // Ikon mata-dicoret saat password terlihat
        const iconHide = `
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5
                c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5
                c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774
                M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21
                m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
        `;

        // Ikon mata biasa saat password tersembunyi
        const iconShow = `
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;

        eyeIcon.innerHTML = isHidden ? iconHide : iconShow;
    }
    </script>

</body>
</html>