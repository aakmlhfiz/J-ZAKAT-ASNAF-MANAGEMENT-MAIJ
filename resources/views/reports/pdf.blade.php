<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan J-ZAKAT AI</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1D1D1F;
            margin: 0;
            padding: 24px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
            color: #0F4C81;
        }
        h2 {
            font-size: 13px;
            margin: 18px 0 8px 0;
            color: #1D1D1F;
            border-bottom: 1px solid #E5E5EA;
            padding-bottom: 4px;
        }
        .meta {
            color: #6E6E73;
            font-size: 10px;
            margin-bottom: 16px;
        }
        .stats {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 18px;
        }
        .stat {
            display: table-cell;
            background: #F5F5F7;
            border-radius: 8px;
            padding: 12px;
            width: 25%;
            vertical-align: top;
        }
        .stat .label {
            font-size: 9px;
            color: #6E6E73;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat .value {
            font-size: 20px;
            font-weight: 700;
            color: #1D1D1F;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }
        th {
            background: #0F4C81;
            color: #fff;
            text-align: left;
            padding: 8px 10px;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        th.r, td.r { text-align: right; }
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #E5E5EA;
        }
        tr:nth-child(even) td { background: #FAFAFC; }
        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #E5E5EA;
            font-size: 9px;
            color: #6E6E73;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Laporan J-ZAKAT AI</h1>
    <p class="meta">Tarikh dijana: {{ now()->format('d F Y, H:i') }} &nbsp;·&nbsp; Majlis Agama Islam Johor (MAIJ)</p>

    <h2>Ringkasan Keseluruhan</h2>
    <div class="stats">
        <div class="stat">
            <div class="label">Jumlah Pemohon</div>
            <div class="value">{{ $totals['jumlah'] }}</div>
        </div>
        <div class="stat">
            <div class="label">Layak</div>
            <div class="value" style="color:#34C759;">{{ $totals['layak'] }}</div>
        </div>
        <div class="stat">
            <div class="label">Tidak Layak</div>
            <div class="value" style="color:#FF3B30;">{{ $totals['tidak_layak'] }}</div>
        </div>
        <div class="stat">
            <div class="label">Semakan</div>
            <div class="value" style="color:#FF9F0A;">{{ $totals['semakan'] }}</div>
        </div>
    </div>

    <h2>Statistik Bulanan (12 Bulan)</h2>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="r">Jumlah</th>
                <th class="r">Layak</th>
                <th class="r">Tidak Layak</th>
                <th class="r">Semakan</th>
                <th class="r">% Layak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthly as $row)
                <tr>
                    <td>{{ $row['bulan'] }}</td>
                    <td class="r">{{ $row['jumlah'] }}</td>
                    <td class="r">{{ $row['layak'] }}</td>
                    <td class="r">{{ $row['tidak'] }}</td>
                    <td class="r">{{ $row['semakan'] }}</td>
                    <td class="r">{{ $row['jumlah'] > 0 ? $row['pct_layak'].'%' : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Senarai Penuh Pemohon ({{ $applicants->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>No. IC</th>
                <th>Telefon</th>
                <th class="r">Pendapatan</th>
                <th class="r">Had Kifayah</th>
                <th>Status</th>
                <th class="r">Skor AI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applicants as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->ic }}</td>
                    <td>{{ $p->telefon ?: '—' }}</td>
                    <td class="r">RM {{ number_format((float) $p->pendapatan, 2) }}</td>
                    <td class="r">RM {{ number_format((float) $p->had_kifayah, 2) }}</td>
                    <td>{{ $p->status_kelayakan }}</td>
                    <td class="r">{{ number_format((float) $p->ai_score, 0) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} J-ZAKAT AI — Smart Asnaf Eligibility System
    </div>
</body>
</html>
