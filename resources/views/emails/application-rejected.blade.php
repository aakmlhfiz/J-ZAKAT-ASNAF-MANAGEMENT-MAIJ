<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Ditolak</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;font-family:'Segoe UI','Inter',system-ui,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="540" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#64748B 0%,#475569 100%);border-radius:20px 20px 0 0;padding:32px;text-align:center;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:28px;font-weight:700;color:#FFFFFF;">DITOLAK</p>
                            <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">J-ZAKAT · Majlis Agama Islam Johor</p>
                        </td>
                    </tr>
                </table>

                <table width="540" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border-radius:0 0 20px 20px;box-shadow:0 1px 2px rgba(0,0,0,0.04),0 12px 32px rgba(0,0,0,0.08);padding:40px 48px;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:20px;font-weight:600;color:#1E293B;">Salam {{ $nama }},</p>

                            <p style="margin:20px 0 0;font-size:15px;color:#64748B;line-height:1.6;">
                                Kami memaklumkan bahawa permohonan bantuan zakat anda telah <strong style="color:#EF4444;">DITOLAK</strong> selepas semakan oleh pihak Majlis Agama Islam Johor (MAIJ).
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background-color:#FEF2F2;border-radius:12px;padding:20px;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">Nama</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">No. K/P</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->ic }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">Status</td>
                                                <td style="padding:6px 0;font-size:13px;color:#EF4444;font-weight:600;text-align:right;">Ditolak</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">Tarikh</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->approved_at->format('d F Y') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if ($profil->approval_remarks)
                            <div style="margin:0 0 24px;padding:16px;background-color:#F1F5F9;border-radius:8px;">
                                <p style="margin:0 0 4px;font-size:12px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.05em;">Sebab Penolakan</p>
                                <p style="margin:0;font-size:14px;color:#475569;line-height:1.5;">{{ $profil->approval_remarks }}</p>
                            </div>
                            @endif

                            <p style="margin:0;font-size:14px;color:#64748B;line-height:1.6;">
                                Anda boleh mengemukakan rayuan atau permohonan baharu dengan dokumen yang lebih lengkap. Sila rujuk notis rasmi yang dilampirkan untuk maklumat lanjut.
                            </p>

                            <div style="margin:32px 0 0;padding-top:24px;border-top:1px solid #E2E8F0;text-align:center;">
                                <p style="margin:0;font-size:12px;color:#94A3B8;">
                                    © {{ date('Y') }} <strong style="color:#1E293B;">J-ZAKAT</strong> · Majlis Agama Islam Johor (MAIJ)
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
