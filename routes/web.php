<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\LaporanController;

// ── Auth (guest only) ──────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

// ── Authenticated ──────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ── Ganti Password Wajib ──────────────────────────────────────────
    Route::get('/change-password', [LoginController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [LoginController::class, 'changePassword'])->name('password.update');

    // Dashboard — semua role, dispatch per role di DashboardController
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Katalog Produk ──────────────────────────────────────────────────
    // Index: semua role
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');

    // PENTING: route statis (create, store, dst) HARUS didaftarkan
    // sebelum route dinamis '/items/{item}'. Kalau tidak, '/items/create'
    // akan "ketangkap" duluan oleh '/items/{item}' (item = "create"),
    // lalu route model binding gagal cari Item dengan id "create" -> 404.
    Route::middleware('can:admin-only')->group(function () {
        Route::resource('items', ItemController::class)->except(['index', 'show']);
    });

    // Show: semua role (ItemController@show memblokir dapur dari ATK/Elektronik)
    // Didaftarkan PALING TERAKHIR di antara route /items/*
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');

    // ── Barang Masuk ────────────────────────────────────────────────────
    // Index: semua role (dapur hanya lihat inbound bahan baku)
    Route::get('/inbounds', [InboundController::class, 'index'])->name('inbounds.index');

    // Create/Store: admin + dapur + owner (gate 'admin-or-dapur')
    Route::middleware('can:admin-or-dapur')->group(function () {
        Route::get('/inbounds/create', [InboundController::class, 'create'])->name('inbounds.create');
        Route::post('/inbounds', [InboundController::class, 'store'])->name('inbounds.store');
    });

    // ── Barang Keluar ───────────────────────────────────────────────────
    // Index: semua role (dapur hanya lihat outbound bahan baku)
    Route::get('/outbounds', [OutboundController::class, 'index'])->name('outbounds.index');

    // Create/Store: admin + dapur + owner (gate 'admin-or-dapur')
    Route::middleware('can:admin-or-dapur')->group(function () {
        Route::get('/outbounds/create', [OutboundController::class, 'create'])->name('outbounds.create');
        Route::post('/outbounds', [OutboundController::class, 'store'])->name('outbounds.store');
    });

    // ── Kasir / POS: admin only ─────────────────────────────────────────
    Route::middleware('can:admin-only')->group(function () {
        Route::get('/kasir', [CashierController::class, 'index'])->name('kasir.index');
        Route::post('/kasir/checkout', [CashierController::class, 'checkout'])->name('kasir.checkout');
        Route::get('/kasir/nota/{transaction}', [CashierController::class, 'nota'])->name('kasir.nota');
    });

    // ── Laporan: admin + owner ──────────────────────────────────────────
    Route::middleware('can:view-laporan')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/stock-by-day', [LaporanController::class, 'stockByDay'])->name('laporan.stock-by-day');
        Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
    });
});