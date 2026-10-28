<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.application_rejected.title') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;font-family:'Segoe UI','Inter',system-ui,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="540" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#64748B 0%,#475569 100%);border-radius:20px 20px 0 0;padding:32px;text-align:center;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:28px;font-weight:700;color:#FFFFFF;">{{ __('emails.application_rejected.header') }}</p>
                            <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">{{ __('emails.application_rejected.subtitle') }}</p>
                        </td>
                    </tr>
                </table>

                <table width="540" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border-radius:0 0 20px 20px;box-shadow:0 1px 2px rgba(0,0,0,0.04),0 12px 32px rgba(0,0,0,0.08);padding:40px 48px;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:20px;font-weight:600;color:#1E293B;">{{ __('emails.common.greeting', ['name' => $nama]) }}</p>

                            <p style="margin:20px 0 0;font-size:15px;color:#64748B;line-height:1.6;">
                                {!! __('emails.application_rejected.intro') !!}
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background-color:#FEF2F2;border-radius:12px;padding:20px;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_rejected.label_name') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_rejected.label_ic') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->ic }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_rejected.label_status') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#EF4444;font-weight:600;text-align:right;">{{ __('emails.application_rejected.status_rejected') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_rejected.label_date') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->approved_at?->locale(app()->getLocale())->translatedFormat('d F Y') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if ($profil->approval_remarks)
                            <div style="margin:0 0 24px;padding:16px;background-color:#F1F5F9;border-radius:8px;">
                                <p style="margin:0 0 4px;font-size:12px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.05em;">{{ __('emails.application_rejected.reason_title') }}</p>
                                <p style="margin:0;font-size:14px;color:#475569;line-height:1.5;">{{ $profil->approval_remarks }}</p>
                            </div>
                            @endif

                            <p style="margin:0;font-size:14px;color:#64748B;line-height:1.6;">
                                {{ __('emails.application_rejected.body') }}
                            </p>

                            <div style="margin:32px 0 0;padding-top:24px;border-top:1px solid #E2E8F0;text-align:center;">
                                <p style="margin:0;font-size:12px;color:#94A3B8;">
                                    {{ __('emails.common.footer_full', ['year' => date('Y')]) }}
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
