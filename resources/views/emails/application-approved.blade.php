<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.application_approved.title') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;font-family:'Segoe UI','Inter',system-ui,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="540" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#34C759 0%,#30B350 100%);border-radius:20px 20px 0 0;padding:32px;text-align:center;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:28px;font-weight:700;color:#FFFFFF;">&#10004; {{ __('emails.application_approved.header') }}</p>
                            <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">{{ __('emails.application_approved.subtitle') }}</p>
                        </td>
                    </tr>
                </table>

                <table width="540" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border-radius:0 0 20px 20px;box-shadow:0 1px 2px rgba(0,0,0,0.04),0 12px 32px rgba(0,0,0,0.08);padding:40px 48px;">
                    <tr>
                        <td>
                            @php
                                $statusKey = 'common.status.'.str_replace([' ', '-'], '_', strtolower((string) $profil->status_kelayakan));
                                $statusLabel = trans()->has($statusKey) ? __($statusKey) : $profil->status_kelayakan;
                            @endphp

                            <p style="margin:0;font-size:20px;font-weight:600;color:#1E293B;">{{ __('emails.common.greeting', ['name' => $nama]) }}</p>

                            <p style="margin:20px 0 0;font-size:15px;color:#64748B;line-height:1.6;">
                                {!! __('emails.application_approved.intro') !!}
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background-color:#F0FDF4;border-radius:8px;padding:20px;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_approved.label_name') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_approved.label_ic') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->ic }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_approved.label_status') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#34C759;font-weight:600;text-align:right;">{{ $statusLabel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#64748B;">{{ __('emails.application_approved.label_date') }}</td>
                                                <td style="padding:6px 0;font-size:13px;color:#1E293B;font-weight:600;text-align:right;">{{ $profil->approved_at?->locale(app()->getLocale())->translatedFormat('d F Y') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:14px;color:#64748B;line-height:1.6;">
                                {{ __('emails.application_approved.body') }}
                            </p>

                            <div style="margin:28px 0 0;padding:16px;background-color:#FFFBEB;border-left:3px solid #D97706;border-radius:8px;">
                                <p style="margin:0;font-size:13px;color:#92400E;line-height:1.5;">
                                    <strong>&#9432; {{ __('emails.application_approved.checklist_title') }}</strong><br>
                                    &middot; {{ __('emails.application_approved.checklist_ic') }}<br>
                                    &middot; {{ __('emails.application_approved.checklist_salary') }}<br>
                                    &middot; {{ __('emails.application_approved.checklist_letter') }}
                                </p>
                            </div>

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
