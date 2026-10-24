@php
    $statusClass = $result['status'] === 'Layak' ? 'success' : 'danger';
@endphp

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Laporan Had Al-Kifayah — J-ZAKAT AI</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Inter', sans-serif;
            color: #1D1D1F;
            margin: 0;
            padding: 32px;
            background: #fff;
        }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 15px; margin: 24px 0 8px; color: #6E6E73; text-transform: uppercase; letter-spacing: 0.5px; }
        h3 { font-size: 13px; margin: 16px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 16px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #E5E5EA; font-size: 13px; }
        th { background: transparent; color: #6E6E73; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #0066CC; padding-bottom: 12px; margin-bottom: 16px; }
        .meta { font-size: 11px; color: #6E6E73; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; }
        .summary .card { padding: 12px; border: 1px solid #E5E5EA; border-radius: 10px; }
        .summary .card .label { font-size: 11px; color: #6E6E73; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary .card .value { font-size: 22px; font-weight: 700; margin-top: 4px; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status.layak { background: #34C759; color: #fff; }
        .status.tidak-layak { background: #FF3B30; color: #fff; }
        ul.reasoning { padding-left: 20px; margin: 8px 0; }
        ul.reasoning li { margin: 4px 0; font-size: 13px; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #E5E5EA; font-size: 11px; color: #6E6E73; text-align: center; }
        @media print {
            body { padding: 16px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="position: fixed; top: 16px; right: 16px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0066CC; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 13px;">
            <i style="font-style: normal;">🖨</i> Cetak
        </button>
    </div>

    <div class="header">
        <div>
            <h1>Laporan Had Al-Kifayah</h1>
            <p class="meta">J-ZAKAT AI — Smart Asnaf Eligibility System</p>
        </div>
        <div class="meta">
            <p>Tarikh: {{ now()->format('d F Y, H:i') }}</p>
            <p>Metodologi: MAIDAM Had Al-Kifayah</p>
        </div>
    </div>

    @php $r = $result; @endphp

    <div class="summary">
        <div class="card">
            <div class="label">Jumlah Pendapatan (A)</div>
            <div class="value">RM {{ number_format($r['pendapatan_total'], 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Jumlah Had Kifayah (C)</div>
            <div class="value">RM {{ number_format($r['had_kifayah_total'], 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Perbezaan (A − C)</div>
            <div class="value">RM {{ number_format($r['baki'], 2) }}</div>
        </div>
    </div>

    <p>
        Status:
        <span class="status {{ $r['status'] === 'Layak' ? 'layak' : 'tidak-layak' }}">
            {{ $r['status'] }}
        </span>
        &nbsp;&nbsp;
        Keyakinan AI: <strong>{{ $r['confidence'] }}%</strong>
    </p>

    <h2>Cadangan</h2>
    <p style="font-size: 14px;">{{ $r['recommendation'] }}</p>

    <h2>Sebab Penilaian</h2>
    <ul class="reasoning">
        @foreach($r['reasoning'] as $line)
            <li>{{ $line }}</li>
        @endforeach
    </ul>

    <h2>Pecahan Keperluan</h2>
    <table>
        <thead>
            <tr>
                <th>Komponen</th>
                <th class="num">Kadar (RM)</th>
                <th class="num">Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($r['breakdown'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ number_format($row['nilai'], 2) }}</td>
                    <td class="num">{{ number_format($row['subtotal'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Input Pengguna</h2>
    <table>
        <thead>
            <tr><th>Medan</th><th class="num">Nilai</th></tr>
        </thead>
        <tbody>
            <tr><td>Pendapatan Pemohon</td><td class="num">RM {{ number_format((float)($input['pendapatan_pemohon'] ?? 0), 2) }}</td></tr>
            <tr><td>Pendapatan Pasangan</td><td class="num">RM {{ number_format((float)($input['pendapatan_pasangan'] ?? 0), 2) }}</td></tr>
            <tr><td>Pendapatan Anak</td><td class="num">RM {{ number_format((float)($input['pendapatan_anak'] ?? 0), 2) }}</td></tr>
            <tr><td>Pendapatan Lain</td><td class="num">RM {{ number_format((float)($input['pendapatan_lain'] ?? 0), 2) }}</td></tr>
            <tr><td>Isteri / Suami</td><td class="num">{{ (int)($input['n_isteri_suami'] ?? 0) }} orang</td></tr>
            <tr><td>Ibu Bapa Tanggungan</td><td class="num">{{ (int)($input['n_ibu_bapa'] ?? 0) }} orang</td></tr>
            <tr><td>Dewasa Tidak Bekerja</td><td class="num">{{ (int)($input['n_dewasa_tidak_bekerja'] ?? 0) }} orang</td></tr>
            <tr><td>Dewasa Bekerja Ditanggung</td><td class="num">{{ (int)($input['n_dewasa_bekerja_tanggung'] ?? 0) }} orang</td></tr>
            <tr><td>IPT</td><td class="num">{{ (int)($input['n_ipt'] ?? 0) }} orang</td></tr>
            <tr><td>Remaja Sekolah Menengah</td><td class="num">{{ (int)($input['n_remaja'] ?? 0) }} orang</td></tr>
            <tr><td>Kanak-Kanak Sekolah Rendah</td><td class="num">{{ (int)($input['n_kanak_rendah'] ?? 0) }} orang</td></tr>
            <tr><td>Kanak-Kanak Tadika</td><td class="num">{{ (int)($input['n_kanak_tadika'] ?? 0) }} orang</td></tr>
            <tr><td>Perumahan</td><td class="num">{{ $input['perumahan'] ?? 'sendiri' }}</td></tr>
            <tr><td>Utiliti</td><td class="num">RM {{ number_format((float)($input['utiliti_input'] ?? 0), 2) }}</td></tr>
            <tr><td>Pengangkutan</td><td class="num">RM {{ number_format((float)($input['pengangkutan_input'] ?? 0), 2) }}</td></tr>
            <tr><td>Perubatan</td><td class="num">RM {{ number_format((float)($input['perubatan_input'] ?? 0), 2) }}</td></tr>
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} J-ZAKAT AI — Smart Asnaf Eligibility System
    </div>
</body>
</html>
