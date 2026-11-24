<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <h2>Laporan Peminjaman Buku Perpustakaan</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporans as $index => $l)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $l->anggota->name
                        ?? $l->anggota->nama
                        ?? $l->user->name
                        ?? $l->user->nama
                        ?? $l->siswa->name
                        ?? $l->siswa->nama
                        ?? $l->nama_anggota
                        ?? '-' }}
                </td>
                <td>{{ $l->buku->judul_buku ?? $l->buku->judul ?? '-' }}</td>
                <td>{{ $l->tanggal_pinjam }}</td>
                <td>{{ $l->tanggal_kembali ?? 'Belum Dikembalikan' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>