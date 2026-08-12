<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Laporan Peminjaman Koleksi</h2>
    <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Member</th>
                <th>Koleksi</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['member'] }}</td>
                <td>{{ $row['collection'] }}</td>
                <td>{{ $row['borrowed_at'] }}</td>
                <td>{{ $row['due_date'] }}</td>
                <td>{{ $row['returned_at'] ?? '-' }}</td>
                <td>{{ ucfirst($row['status']) }}</td>
                <td>Rp {{ number_format($row['fine_amount'], 0, ',', '.') }} ({{ $row['fine_status'] }})</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
