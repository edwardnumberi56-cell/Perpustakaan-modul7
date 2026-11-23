<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku; 
use Illuminate\Support\Facades\DB; // Tambahkan ini di bagian atas

class BukuController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input parameter pencarian dari user
        $keyword = $request->input('search');

        // RENTAN SQLi: Menggunakan DB::select dengan string interpolation
        if ($keyword) {
            $bukus = DB::select("SELECT * FROM bukus WHERE judul LIKE '%$keyword%'");
        } else {
            $bukus = DB::select("SELECT * FROM bukus");
        }

        // Tambahan: ambil data kategori langsung dari tabel kategoris di database
        $kategoris = DB::table('kategoris')->orderBy('id')->get(); //[cite: 4]

        return view('buku.index', compact('bukus', 'kategoris'));
    }

    public function cari(Request $request)
    {
        $keyword = $request->input('judul');

        // MENGGUNAKAN PARAMETER BINDING (?)
        $buku = DB::select("SELECT * FROM bukus WHERE judul LIKE ?", ['%' . $keyword . '%']);

        return response()->json($buku);
    }

    // A3: Method destroy untuk menghapus buku
    public function destroy(Request $request, $buku)
    {
        $bukuModel = Buku::findOrFail($buku);
        $bukuModel->delete();

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dihapus.'
            ]);
        }

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        // Validasi input data yang dikirim dari form edit
        $request->validate([
            'kategori_id' => 'required|integer',
            'judul' => 'required|string|min:3|max:255',
            'penulis' => 'required|string|min:3|max:255',
            'tahun_terbit' => 'required|integer|max:2026',
        ]);

        // Proses update data menggunakan Raw SQL sesuai dengan gaya project Anda
        DB::update(
            "UPDATE bukus SET kategori_id = ?, judul = ?, penulis = ?, tahun_terbit = ?, updated_at = NOW() WHERE id = ?",
            [
                $request->kategori_id,
                $request->judul,
                $request->penulis,
                $request->tahun_terbit,
                $id
            ]
        );
        
        // A3: Method destroy untuk menghapus buku
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Data buku berhasil diperbarui!'
            ]);
        }

        // Redirect kembali ke halaman daftar buku dengan pesan sukses
        return redirect()->route('buku.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        // Validasi input data
        $request->validate([
            'kategori_id' => 'required|integer',
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|max:2026',
        ]);

        // Proses insert menggunakan Raw SQL
        DB::insert(
            "INSERT INTO bukus (kategori_id, judul, penulis, tahun_terbit, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())",
            [
                $request->kategori_id,
                $request->judul,
                $request->penulis,
                $request->tahun_terbit
            ]
        );

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil ditambahkan!'
            ], 201);
        }

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan!');
    }


// TARUH METHOD CETAK LAPORAN DI SINI:
    public function cetakLaporan()
    {
        // Cek apakah user yang mengakses memiliki izin kelola-buku (Admin/Petugas)
        if (!auth()->user()->can('kelola-buku')) {
            abort(403, 'Akses ditolak. Fitur cetak laporan hanya untuk Admin dan Petugas.');
        }
    }
}; // <-- Perhatikan: Sekarang hanya ada 1 penutup kurung kurawal untuk class di paling bawah.