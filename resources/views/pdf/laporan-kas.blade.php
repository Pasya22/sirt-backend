<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; }
        h2 { margin-bottom: 4px; }
        .sub { color: #64748b; margin-bottom: 20px; }
        .summary { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary td { padding: 8px 12px; border: 1px solid #cbd5e1; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        table.data th { background: #f1f5f9; }
        .right { text-align: right; }
        .masuk { color: #059669; font-weight: bold; }
        .keluar { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Laporan Kas RT 01 / RW 08</h2>
    <div class="sub">Periode: {{ $laporan['periode'] }}</div>

    <table class="summary">
        <tr>
            <td><strong>Saldo Awal:</strong> Rp {{ number_format($laporan['saldoAwal'], 0, ',', '.') }}</td>
            <td><strong>Total Masuk:</strong> Rp {{ number_format($laporan['totalMasuk'], 0, ',', '.') }}</td>
            <td><strong>Total Keluar:</strong> Rp {{ number_format($laporan['totalKeluar'], 0, ',', '.') }}</td>
            <td><strong>Saldo Akhir:</strong> Rp {{ number_format($laporan['saldoAkhir'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th class="right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan['transaksi'] as $trx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d M Y') }}</td>
                    <td>{{ $trx->ket }}</td>
                    <td>{{ $trx->kategori }}</td>
                    <td class="right {{ $trx->tipe === 'masuk' ? 'masuk' : 'keluar' }}">
                        {{ $trx->tipe === 'masuk' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>