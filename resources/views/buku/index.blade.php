@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Manajemen Data Buku (RESTful API)</h2>

    <!-- Form Pencarian Sederhana -->
    <form action="{{ route('buku.index') }}" method="GET" style="margin-bottom: 15px;">
        <input type="text" name="search" placeholder="Cari buku..." style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="submit" style="padding: 5px 10px; background: #3b82f6; color: white; border: none; border-radius: 4px;">Cari</button>
    </form>

    {{-- Pesan Notifikasi Sukses / Error --}}
    <div id="alert-container" class="mt-3"></div>

    <div class="row mt-3">
    
    <!-- KOLOM KIRI: Form Tambah Buku -->
    @can('kelola-buku')
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" id="form-title">Tambah Buku Baru</div>
            <div class="card-body">
                <form id="buku-form">
                    <input type="hidden" id="buku-id">
                    
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">ID Kategori</label>
                        <input type="number" class="form-control" id="kategori_id" required>
                    </div>

                    {{-- Tambahan: dropdown Kategori langsung dari tabel kategoris --}}
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-control" id="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoris ?? [] as $kategori)
                                <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Buku</label>
                        <input type="text" class="form-control" id="judul" required>
                    </div>

                    <div class="mb-3">
                        <label for="penulis" class="form-label">Penulis</label>
                        <input type="text" class="form-control" id="penulis" required>
                    </div>

                    <div class="mb-3">
                        <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                        <input type="number" class="form-control" id="tahun_terbit" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btn-submit">Simpan</button>
                    <button type="button" class="btn btn-secondary w-100 mt-2 d-none" id="btn-cancel" onclick="resetForm()">Batal</button>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <!-- KOLOM KANAN: Tabel Daftar Buku -->
    @can('kelola-buku')
        <div class="col-md-8">
    @else
        <div class="col-md-12">
    @endcan
        <div class="card">
            <div class="card-header">Daftar Buku Perpustakaan</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 20%;">Kategori</th>
                                <th style="width: 30%;">Judul Buku</th>
                                <th style="width: 20%;">Penulis</th>
                                <th style="width: 10%;">Tahun</th>
                                <th style="width: 15%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="buku-table-body">
                            @foreach($bukus as $index => $buku)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $buku->kategori->nama_kategori ?? $buku->kategori_id }}</td>
                                    <td>{{ $buku->judul }}</td>
                                    <td>{{ $buku->penulis }}</td>
                                    <td>{{ $buku->tahun_terbit }}</td>
                                    <td class="text-center">
                                        @can('kelola-buku')
                                            <a href="{{ route('buku.edit', $buku->id) }}" class="btn btn-warning btn-sm text-white">Ubah</a>
                                            <form action="{{ route('buku.destroy', $buku->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                            </form>
                                        @endcan
                                        @role('siswa')
                                            <form action="{{ route('siswa.pinjam', $buku->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Pinjam Buku</button>
                                            </form>
                                        @endrole
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Skrip JavaScript Fetch API, async/await, dan Penanganan Error --}}
<script>
    const API_URL = '/api/buku';
    
    const canKelolaBuku = @json(
        auth()->user()->can('kelola-buku') || 
        auth()->user()->hasRole('admin') ||     
        auth()->user()->hasRole('petugas')
    );

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    async function loadBuku() {
        try {
            let response = await fetch(API_URL);
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');

            let result = await response.json();
            
            let data = [];
            if (result.data && Array.isArray(result.data)) {
                data = result.data; 
            } else if (result.data && result.data.data && Array.isArray(result.data.data)) {
                data = result.data.data; 
            } else if (Array.isArray(result)) {
                data = result;
            }

            let tbody = document.getElementById('buku-table-body');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">Belum ada data buku.</td></tr>`;
                return;
            }

            data.forEach((buku, index) => {
                let kategori = buku.kategori?.nama_kategori ?? buku.kategori?.nama ?? buku.kategori_id ?? '-';
                let judul = buku.judul_buku ?? buku.judul ?? '-';
                let penulis = buku.penulis ?? '-';
                let tahun = buku.tahun ?? buku.tahun_terbit ?? '-';

                let displayKategori = escapeHtml(kategori);
                let displayJudul = escapeHtml(judul);
                let displayPenulis = escapeHtml(penulis);

                let safeJudul = String(judul).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                let safePenulis = String(penulis).replace(/\\/g, '\\\\').replace(/'/g, "\\'");

                let actionHtml = '';
                if (canKelolaBuku) {
                    actionHtml = `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-warning btn-sm text-white" onclick="editBuku(${buku.id}, '${buku.kategori_id ?? ''}', '${safeJudul}', '${safePenulis}', ${tahun})">Ubah</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteBuku(${buku.id})">Hapus</button>
                        </div>
                    `;
                } else {
                    actionHtml = `
                        <form action="/siswa/pinjam/${buku.id}" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''}">
                            <button type="submit" class="btn btn-success btn-sm">Pinjam Buku</button>
                        </form>
                    `;
                }

                tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${displayKategori}</td>
                    <td>${displayJudul}</td>
                    <td>${displayPenulis}</td>
                    <td>${tahun}</td>
                    <td class="text-center">
                        ${actionHtml}
                    </td>
                </tr>
                `;
            });
        } catch (error) {
            console.error('Kesalahan loadBuku:', error);
        }
    }

    function resetForm() {
        let form = document.getElementById('buku-form');
        if (form) form.reset();
        let bukuId = document.getElementById('buku-id');
        if (bukuId) bukuId.value = '';
        let formTitle = document.getElementById('form-title');
        if (formTitle) formTitle.innerText = 'Tambah Buku Baru';
        let btnCancel = document.getElementById('btn-cancel');
        if (btnCancel) btnCancel.classList.add('d-none');
    }

    function editBuku(id, kategori_id, judul, penulis, tahun_terbit) {
        document.getElementById('buku-id').value = id;
        document.getElementById('kategori_id').value = kategori_id;
        document.getElementById('judul').value = judul;
        document.getElementById('penulis').value = penulis;
        document.getElementById('tahun_terbit').value = tahun_terbit;
        let formTitle = document.getElementById('form-title');
        if (formTitle) formTitle.innerText = 'Ubah Data Buku';
        let btnCancel = document.getElementById('btn-cancel');
        if (btnCancel) btnCancel.classList.remove('d-none');
    }

    async function deleteBuku(id) {
        if (!confirm('Apakah kamu yakin ingin menghapus buku ini?')) return;

        try {
            let response = await fetch(`${API_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            let result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal menghapus data.');

            loadBuku(); 
        } catch (error) {
            alert('Kesalahan: ' + error.message);
        }
    }

    document.getElementById('buku-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        let id = document.getElementById('buku-id').value;
        let kategori_id = document.getElementById('kategori_id').value;
        let judul = document.getElementById('judul').value;
        let penulis = document.getElementById('penulis').value;
        let tahun_terbit = parseInt(document.getElementById('tahun_terbit').value);

        if (tahun_terbit > 2026) {
            alert('Validasi Gagal:\nTahun terbit tidak boleh lebih dari tahun 2026!');
            return; 
        }

        if (id) {
            if (!confirm('Apakah kamu yakin ingin memperbarui data buku ini?')) {
                return;
            }
        }

        let payload = { kategori_id, judul, penulis, tahun_terbit };
        let url = id ? `${API_URL}/${id}` : API_URL;
        let method = id ? 'PUT' : 'POST';

        try {
            let response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(payload)
            });

            let result = await response.json();

            if (response.status === 422) {
                let errorMessages = '';
                for (let field in result.errors) {
                    errorMessages += `- ${result.errors[field][0]}\n`;
                }
                alert("Validasi Gagal:\n" + errorMessages);
                return;
            }

            if (!response.ok) {
                throw new Error(result.message || 'Terjadi kesalahan saat menyimpan data.');
            }

            alert(id ? 'Buku berhasil diperbarui!' : 'Buku berhasil ditambahkan!');
            resetForm();
            loadBuku();

        } catch (error) {
            alert('Kesalahan: ' + error.message);
        }
    });

    document.addEventListener('DOMContentLoaded', loadBuku);
</script>
@endsection