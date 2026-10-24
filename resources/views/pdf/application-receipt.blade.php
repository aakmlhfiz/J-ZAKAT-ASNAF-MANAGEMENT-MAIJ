<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Resit Permohonan</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 13px; color: #1E293B; margin: 0; padding: 0; }
        .container { padding: 24px 32px; }
        .header { text-align: center; border-bottom: 3px solid #122D70; padding-bottom: 16px; margin-bottom: 24px; }
        .header .brand { font-size: 22px; font-weight: bold; color: #122D70; }
        .header .org { font-size: 12px; color: #64748B; margin-top: 4px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; letter-spacing: 1px; margin: 20px 0 24px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; vertical-align: top; }
        .label { color: #64748B; width: 200px; }
        .value { font-weight: bold; }
        .divider { border-top: 1px dashed #CBD5E1; margin: 20px 0; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #94A3B8; }
        .footer .mark { color: #122D70; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">J-ZAKAT</div>
            <div class="org">Majlis Agama Islam Johor (MAIJ)</div>
        </div>

        <div class="title">RESIT PERMOHONAN BANTUAN ZAKAT</div>

        <table>
            <tr><td class="label">No. Permohonan</td><td class="value">{{ $application->application_no }}</td></tr>
            <tr><td class="label">Nama Pemohon</td><td class="value">{{ $application->full_name }}</td></tr>
            <tr><td class="label">No. Kad Pengenalan</td><td class="value">{{ $application->ic_no }}</td></tr>
            <tr><td class="label">Jenis Bantuan</td><td class="value">{{ $application->assistance_type ?: 'Permohonan Bantuan Zakat' }}</td></tr>
            <tr><td class="label">Tarikh Dihantar</td><td class="value">{{ $application->submitted_at?->format('d M Y, h:i A') }}</td></tr>
            <tr><td class="label">Status</td><td class="value">
                {{ match($application->status) {
                    'submitted' => 'Dihantar',
                    'under_review' => 'Dalam Semakan',
                    'approved' => 'Diluluskan',
                    'rejected' => 'Ditolak',
                    default => ucfirst($application->status),
                } }}
            </td></tr>
        </table>

        <div class="divider"></div>
        <p style="font-size: 12px; color: #64748B;">
            Resit ini merupakan pengesahan rasmi penerimaan permohonan anda oleh sistem J-ZAKAT.
            Simpan resit ini sebagai rujukan. Keputusan kelayakan muktamad adalah tertakluk kepada penilaian rasmi MAIJ.
        </p>

        <div class="footer">
            <p>Dijana pada {{ $generated_at->format('d M Y, h:i A') }} · <span class="mark">SULIT — Penggunaan Dalaman MAIJ</span></p>
            <p>&copy; {{ date('Y') }} J-ZAKAT · Majlis Agama Islam Johor</p>
        </div>
    </div>
</body>
</html>
