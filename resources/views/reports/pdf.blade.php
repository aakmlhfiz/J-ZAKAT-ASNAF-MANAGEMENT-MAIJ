<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdf.report.meta_title') }}</title>
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
    <h1>{{ __('pdf.report.title') }}</h1>
    <p class="meta">{{ __('pdf.report.generated_at', ['datetime' => now()->locale(app()->getLocale())->translatedFormat('d F Y, H:i')]) }} &nbsp;·&nbsp; {{ __('pdf.report.org') }}</p>

    <h2>{{ __('pdf.report.summary') }}</h2>
    <div class="stats">
        <div class="stat">
            <div class="label">{{ __('pdf.report.total_applicants') }}</div>
            <div class="value">{{ $totals['jumlah'] }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('pdf.report.eligible') }}</div>
            <div class="value" style="color:#34C759;">{{ $totals['layak'] }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('pdf.report.not_eligible') }}</div>
            <div class="value" style="color:#FF3B30;">{{ $totals['tidak_layak'] }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('pdf.report.review') }}</div>
            <div class="value" style="color:#FF9F0A;">{{ $totals['semakan'] }}</div>
        </div>
    </div>

    <h2>{{ __('pdf.report.monthly') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('pdf.report.month') }}</th>
                <th class="r">{{ __('pdf.report.total') }}</th>
                <th class="r">{{ __('pdf.report.eligible') }}</th>
                <th class="r">{{ __('pdf.report.not_eligible') }}</th>
                <th class="r">{{ __('pdf.report.review') }}</th>
                <th class="r">{{ __('pdf.report.pct_eligible') }}</th>
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

    <h2>{{ __('pdf.report.full_list', ['count' => $applicants->count()]) }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('pdf.report.name') }}</th>
                <th>{{ __('pdf.report.ic') }}</th>
                <th>{{ __('pdf.report.phone') }}</th>
                <th class="r">{{ __('pdf.report.income') }}</th>
                <th class="r">{{ __('pdf.report.kifayah') }}</th>
                <th>{{ __('pdf.report.status') }}</th>
                <th class="r">{{ __('pdf.report.ai_score') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applicants as $p)
                @php
                    $statusKey = 'common.status.'.str_replace([' ', '-'], '_', strtolower((string) $p->status_kelayakan));
                    $statusLabel = trans()->has($statusKey) ? __($statusKey) : $p->status_kelayakan;
                @endphp
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->ic }}</td>
                    <td>{{ $p->telefon ?: '—' }}</td>
                    <td class="r">RM {{ number_format((float) $p->pendapatan, 2) }}</td>
                    <td class="r">RM {{ number_format((float) $p->had_kifayah, 2) }}</td>
                    <td>{{ $statusLabel }}</td>
                    <td class="r">{{ number_format((float) $p->ai_score, 0) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ __('pdf.report.copyright', ['year' => date('Y'), 'platform' => __('common.footer.platform')]) }}
    </div>
</body>
</html>
