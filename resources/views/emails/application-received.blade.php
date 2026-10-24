<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Diterima</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;font-family:'Segoe UI','Inter',system-ui,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="540" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#56E1E8 0%,#3FA9F5 35%,#5C58ED 70%,#122D70 100%);border-radius:20px 20px 0 0;padding:32px;text-align:center;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:28px;font-weight:700;color:#FFFFFF;">J-ZAKAT AI</p>
                            <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.7);">Pengesahan Permohonan</p>
                        </td>
                    </tr>
                </table>

                <table width="540" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border-radius:0 0 20px 20px;box-shadow:0 1px 2px rgba(0,0,0,0.04),0 12px 32px rgba(0,0,0,0.08);padding:40px 48px;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:20px;font-weight:600;color:#1E293B;">Permohonan Anda Telah Diterima</p>

                            <p style="margin:20px 0 0;font-size:15px;color:#64748B;line-height:1.6;">
                                Salam sejahtera <strong>{{ $application->full_name }}</strong>, permohonan bantuan zakat anda telah berjaya dihantar dan sedang menunggu semakan pihak Majlis Agama Islam Johor (MAIJ).
                            </p>

                            <div style="margin:28px 0;padding:20px;background-color:#F1F5F9;border-left:4px solid #22C55E;border-radius:8px;">
                                <p style="margin:0;font-size:13px;color:#64748B;">No. Rujukan Permohonan</p>
                                <p style="margin:6px 0 0;font-size:18px;font-weight:700;color:#1E293B;font-family:'Courier New',monospace;">#{{ str_pad((string) $application->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </div>

                            <p style="margin:0;font-size:14px;color:#64748B;line-height:1.6;">
                                Anda boleh menyemak status permohonan anda pada bila-bila masa melalui portal pemohon.
                            </p>

                            <div style="margin:32px 0 0;text-align:center;">
                                <a href="{{ $portalUrl }}" style="display:inline-block;padding:14px 36px;background-color:#2563EB;color:#FFFFFF;font-size:14px;font-weight:600;text-decoration:none;border-radius:12px;">Semak Status Permohonan</a>
                            </div>

                            <div style="margin:32px 0 0;padding-top:24px;border-top:1px solid #E2E8F0;text-align:center;">
                                <p style="margin:0;font-size:12px;color:#94A3B8;">
                                    &copy; {{ date('Y') }} <strong style="color:#1E293B;">J-ZAKAT AI</strong> &middot; Majlis Agama Islam Johor
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