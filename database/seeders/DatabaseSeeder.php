<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            KategoriSeeder::class,
            AnggotaSeeder::class,
            BukuSeeder::class,
            PeminjamanSeeder::class,
        ]);

        // Memastikan akun admin menggunakan password baru dari Modul 6
        User::where('email', 'admin@perpus.com')->update([
            'password' => Hash::make('AdminBaru2026!#'),
        ]);
    }
}