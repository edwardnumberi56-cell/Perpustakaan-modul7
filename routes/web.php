<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\Api\BukuController as ApiBukuController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaPeminjamanController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


Route::middleware(['auth'])->group(function () {

    // PROFIL PENGGUNA
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // DAFTAR BUKU (SEMUA ROLE YANG SUDAH LOGIN)
    Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');

    // Pencarian buku
    Route::get('/buku-cari', [BukuController::class, 'cari']);


    // A3: CSRF - Route GET hapus buku (untuk pengujian keamanan)
    // Dibatasi untuk Admin dan Petugas
    Route::middleware(['can:kelola-buku'])->group(function () {
        Route::get('/buku/{buku}/hapus', [BukuController::class, 'destroy'])->name('buku.hapus');
    });


    // A4: IDOR - Route detail peminjaman untuk pengujian
    Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show'])
        ->name('peminjaman.show');


    // KHUSUS ADMIN DAN PETUGAS
    Route::middleware(['can:kelola-buku'])->group(function () {

        // MANAJEMEN BUKU
        Route::get('/buku/create', [BukuController::class, 'create'])->name('buku.create');
        Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
        Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])->name('buku.edit');
        Route::put('/buku/{buku}', [BukuController::class, 'update'])->name('buku.update');
        Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');


        // PEMINJAMAN
        Route::resource('peminjaman', PeminjamanController::class)->except(['show']);


        // ADMIN PEMINJAMAN
        Route::get('/admin/peminjaman', [PeminjamanController::class, 'index'])
            ->name('admin.peminjaman.index');

        Route::put('/admin/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembalikan'])
            ->name('admin.peminjaman.kembali');


        // LAPORAN
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');


        // API BUKU
        Route::post('/api/buku', [ApiBukuController::class, 'store']);
        Route::put('/api/buku/{id}', [ApiBukuController::class, 'update']);
        Route::delete('/api/buku/{id}', [ApiBukuController::class, 'destroy']);

    });


    // KHUSUS ADMIN
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/admin/users', [UserController::class, 'index'])
            ->name('admin.users.index');

        Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])
            ->name('admin.users.edit');

        Route::put('/admin/users/{id}', [UserController::class, 'update'])
            ->name('admin.users.update');

    });


    // KHUSUS SISWA
    Route::middleware(['role:siswa'])->group(function () {

        Route::get('/riwayat-peminjaman', [PeminjamanController::class, 'riwayat'])
            ->name('siswa.peminjaman.riwayat');

        Route::get('/peminjaman/{id}/detail', [PeminjamanController::class, 'show'])
            ->name('siswa.peminjaman.detail');

        Route::post('/buku/{id}/pinjam', [PeminjamanController::class, 'storeSiswa'])
            ->name('siswa.pinjam');

        Route::get('/laporan/rekap', [PeminjamanController::class, 'laporan'])
            ->name('laporan.rekap');

    });

    // CONTOH YANG BENAR (Otomatis memicu Policy):
    Route::get('/laporan/{peminjaman}', [LaporanController::class, 'show'])->name('laporan.show');

    Route::post('/siswa/pinjam/{buku}', [SiswaPeminjamanController::class, 'store'])->name('siswa.pinjam'); // Atau method 'store'/'pinjam'
});

Route::get('/test-debug', function () {
    throw new \RuntimeException('Uji coba debug error');
});

Route::get('/cek-session', function () {
    return response()->json([
        'app_key_set' => !empty(config('app.key')),
        'session_driver' => config('session.driver'),
        'session_secure' => config('session.secure'),
        'session_domain' => config('session.domain'),
    ]);
});


require __DIR__.'/auth.php';