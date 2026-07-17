<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password — Rajawali</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <div class="flex flex-col items-center text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-600/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-black tracking-tight text-slate-900 leading-snug">WAJIB GANTI PASSWORD</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Akun Anda masih menggunakan password bawaan seeder</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 p-8 rounded-3xl shadow-sm space-y-6">
            @if(session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium p-4 rounded-xl">
                    {{ session('warning') }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Current Password -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Password Saat Ini</label>
                    <input type="password" name="current_password" required class="w-full bg-slate-50 border border-slate-200 text-xs px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all font-medium">
                    @error('current_password')
                        <span class="text-[10px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Password Baru</label>
                    <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-200 text-xs px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all font-medium">
                    <span class="text-[9px] text-slate-400 block font-semibold">Kombinasi huruf & angka, minimal 8 karakter.</span>
                    @error('password')
                        <span class="text-[10px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required class="w-full bg-slate-50 border border-slate-200 text-xs px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all font-medium">
                </div>

                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 rounded-xl transition-all shadow-md shadow-amber-600/15 cursor-pointer">
                    PERBARUI PASSWORD & MASUK KE SISTEM
                </button>
            </form>
        </div>
    </div>
</body>
</html>
