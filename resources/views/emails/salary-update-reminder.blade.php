<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.salary_update.title') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;font-family:'Segoe UI','Inter',system-ui,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="540" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#56E1E8 0%,#3FA9F5 35%,#5C58ED 70%,#122D70 100%);border-radius:20px 20px 0 0;padding:32px;text-align:center;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:28px;font-weight:700;color:#FFFFFF;">J-ZAKAT</p>
                            <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.7);">{{ __('emails.salary_update.header') }}</p>
                        </td>
                    </tr>
                </table>

                <table width="540" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border-radius:0 0 20px 20px;box-shadow:0 1px 2px rgba(0,0,0,0.04),0 12px 32px rgba(0,0,0,0.08);padding:40px 48px;">
                    <tr>
                        <td>
                            <p style="margin:0;font-size:20px;font-weight:600;color:#1E293B;">{{ __('emails.common.greeting', ['name' => $nama]) }}</p>

                            <p style="margin:20px 0 0;font-size:15px;color:#64748B;line-height:1.6;">
                                {!! __('emails.salary_update.intro', ['ic' => e($ic)]) !!}
                            </p>

                            <div style="margin:24px 0;padding:20px;background-color:#FEF3C7;border-left:4px solid #F59E0B;border-radius:8px;">
                                <p style="margin:0 0 12px 0;font-size:13px;font-weight:700;color:#92400E;text-transform:uppercase;letter-spacing:0.5px;">{{ __('emails.salary_update.why_title') }}</p>
                                <p style="margin:0;font-size:15px;color:#1E293B;line-height:1.6;">
                                    {!! __('emails.salary_update.why_body', ['months' => $months]) !!}
                                </p>
                            </div>

                            <p style="margin:0;font-size:14px;color:#64748B;line-height:1.6;">
                                {{ __('emails.salary_update.body') }}
                                {{ __('emails.common.support', ['phone' => '07-228 2928']) }}
                            </p>

                            <div style="margin:28px 0 0;text-align:center;">
                                <a href="{{ $loginUrl }}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#3FA9F5 0%,#5C58ED 100%);color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:600;border-radius:8px;">{{ __('emails.salary_update.button') }}</a>
                            </div>

                            <div style="margin:32px 0 0;padding-top:24px;border-top:1px solid #E2E8F0;text-align:center;">
                                <p style="margin:0;font-size:12px;color:#94A3B8;">
                                    {{ __('emails.common.footer', ['year' => date('Y')]) }}
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
