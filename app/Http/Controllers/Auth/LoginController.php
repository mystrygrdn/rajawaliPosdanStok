<?php
// File: app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses login dengan perlindungan Brute Force.
     *
     * PENJELASAN KEAMANAN:
     * - RateLimiter membatasi percobaan login maksimal 5 kali per menit
     *   per kombinasi username + IP address.
     * - Setelah 5 kali gagal, user dikunci selama 60 detik.
     * - session()->regenerate() mencegah Session Fixation Attack:
     *   yaitu serangan di mana penyerang "menanamkan" session ID miliknya
     *   ke browser korban sebelum korban login, lalu ikut masuk setelah
     *   korban berhasil login. Dengan regenerate(), session ID lama
     *   dibuang dan diganti baru setelah login berhasil.
     */
    public function login(Request $request)
    {
        // Validasi input dasar
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // --- PROTEKSI BRUTE FORCE ---
        // Kunci unik berdasarkan username + IP, huruf kecil semua
        $throttleKey = Str::lower($request->input('username')) . '|' . $request->ip();

        // Cek apakah sudah melebihi batas percobaan (5 kali)
        if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Coba autentikasi dengan field 'username' (sesuai form HTML dan database)
        $berhasilLogin = Auth::attempt(
            [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ],
            $request->boolean('remember') // untuk fitur "Ingat Perangkat Ini"
        );

        if ($berhasilLogin) {
            // Reset hitungan gagal karena login berhasil
            RateLimiter::clear($throttleKey);

            // WAJIB: Regenerasi session ID untuk cegah Session Fixation
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Login gagal: tambah hitungan percobaan, decay 60 detik
        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'username' => 'Username atau password salah.',
        ]);
    }

    /**
     * Logout: hancurkan session sepenuhnya.
     *
     * PENJELASAN KEAMANAN:
     * - invalidate() menghapus semua data session dari storage.
     * - regenerateToken() mengganti CSRF token agar token lama
     *   yang mungkin sudah bocor tidak bisa dipakai lagi.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Menampilkan form ganti password wajib.
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Menyimpan password baru hasil reset mandiri pengguna.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                'confirmed',
                // Validasi kekuatan password: Minimal 8 karakter, harus ada huruf dan angka
                \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers(),
            ],
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Simpan password baru terenkripsi
        $user->password = \Illuminate\Support\Facades\Hash::make($request->input('password'));
        $user->must_change_password = false; // Tandai sudah diubah
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Password default berhasil diganti. Sekarang Anda dapat menggunakan sistem.');
    }
}