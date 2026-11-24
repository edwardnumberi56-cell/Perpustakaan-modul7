@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Ubah Data Buku</div>
                <div class="card-body">
                    {{-- Perhatikan action mengarah ke rute update buku --}}
                    <form action="{{ route('buku.update', $buku->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="kategori_id" class="form-label">ID Kategori</label>
                            <input type="number" class="form-control" id="kategori_id" name="kategori_id" value="{{ old('kategori_id', $buku->kategori_id ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control" id="judul" name="judul" value="{{ old('judul', $buku->judul ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="penulis" class="form-label">Penulis</label>
                            <input type="text" class="form-control" id="penulis" name="penulis" value="{{ old('penulis', $buku->penulis ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit ?? '') }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Perbarui Buku</button>
                        <a href="{{ route('buku.index') }}" class="btn btn-secondary w-100 mt-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection