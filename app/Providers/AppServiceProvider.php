<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Auto-seed database SQLite kosong milik customer saat aplikasi
        // pertama kali dibuka (fresh install). Tidak akan terpicu lagi
        // setelah ada user di database.
        try {
            if (Schema::hasTable('users') && User::count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Exception $e) {
            // Database belum termigrasi — abaikan, biarkan proses migrate berjalan dulu
        }
    }
}