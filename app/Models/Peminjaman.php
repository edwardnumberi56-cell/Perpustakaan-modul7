<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $fillable = [
        'anggota_id',
        'buku_id',
        'tanggal_pinjam',
        'tanggal_kembali',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anggota_id');
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }

    // --- TAMBAHAN DI BAWAH INI (TANPA MENGUBAH KODE LAMA) ---
    public function getNamaAnggotaAttribute()
    {
        if ($this->anggota) {
            return $this->anggota->name ?? $this->anggota->nama ?? '-';
        }

        if ($this->anggota_id) {
            $anggotaTabel = \Illuminate\Support\Facades\DB::table('anggotas')
                ->where('id', $this->anggota_id)
                ->first();

            if ($anggotaTabel) {
                return $anggotaTabel->nama ?? $anggotaTabel->name ?? '-';
            }
        }

        return '-';
    }
}