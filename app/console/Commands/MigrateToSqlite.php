<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToSqlite extends Command
{
    /**
     * Nama dan tanda tangan dari console command.
     */
    protected $signature = 'db:migrate-mysql-to-sqlite';

    /**
     * Deskripsi console command.
     */
    protected $description = 'Migrasi data dari database MySQL lama ke SQLite secara interaktif';

    /**
     * Eksekusi console command.
     */
    public function handle()
    {
        $this->info('=== LOGIKA MIGRASI DATA MYSQL KE SQLITE ===');

        $host = $this->ask('Masukkan MySQL Host lama', '127.0.0.1');
        $port = $this->ask('Masukkan MySQL Port lama', '3306');
        $db = $this->ask('Masukkan nama database MySQL lama', 'rajawali_stok');
        $user = $this->ask('Masukkan MySQL Username lama', 'root');
        $password = $this->secret('Masukkan MySQL Password lama (kosongkan jika tidak ada)') ?? '';

        // Setup konfigurasi koneksi mysql_old secara dinamis di runtime
        config(['database.connections.mysql_old' => [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $db,
            'username' => $user,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]]);

        try {
            $this->info('Mencoba menghubungkan ke MySQL lama...');
            DB::connection('mysql_old')->getPdo();
            $this->info('Koneksi ke MySQL lama berhasil!');
        } catch (\Exception $e) {
            $this->error('Gagal terhubung ke MySQL lama: ' . $e->getMessage());
            return 1;
        }

        if (!$this->confirm('Apakah Anda yakin ingin menimpa data di SQLite dengan data dari MySQL? Semua data SQLite saat ini akan dihapus!', true)) {
            $this->info('Migrasi dibatalkan.');
            return 0;
        }

        $this->info('Memulai pemindahan data...');

        try {
            DB::beginTransaction();

            // Matikan foreign key check di SQLite agar tidak gagal karena urutan import tabel
            DB::statement('PRAGMA foreign_keys = OFF;');

            $tables = ['users', 'items', 'inbounds', 'outbounds', 'sale_transactions'];

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    $this->warn("Tabel {$table} tidak ditemukan di SQLite, dilewati.");
                    continue;
                }

                // Bersihkan tabel SQLite terlebih dahulu
                DB::table($table)->truncate();

                // Ambil data dari MySQL
                $rows = DB::connection('mysql_old')->table($table)->get();

                $this->info("Menyalin tabel {$table} ({$rows->count()} baris)...");

                foreach ($rows as $row) {
                    DB::table($table)->insert((array)$row);
                }
            }

            // Aktifkan kembali foreign key check di SQLite
            DB::statement('PRAGMA foreign_keys = ON;');

            DB::commit();
            $this->info('Migrasi data selesai dengan sukses!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan saat migrasi data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}