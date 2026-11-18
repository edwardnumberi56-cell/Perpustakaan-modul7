<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Pastikan facade ini di-import
use App\Models\Peminjaman;          // Tambahkan import Model Peminjaman
use App\Policies\PeminjamanPolicy;  // Tambahkan import Policy
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // <-- 2. Tambahkan baris ini agar Laravel memaksa HTTPS di Vercel
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        // Daftarkan Policy Peminjaman untuk soal nomor 4
        Gate::policy(Peminjaman::class, PeminjamanPolicy::class);

        // Mendefinisikan Gate kelola-buku untuk admin dan petugas
        Gate::define('kelola-buku', function ($user) {
            return $user->hasRole(['admin', 'petugas']);
        });
    }
}