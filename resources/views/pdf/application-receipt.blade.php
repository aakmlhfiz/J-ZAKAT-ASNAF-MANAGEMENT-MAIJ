<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdf.receipt.meta_title') }}</title>
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
            <div class="org">{{ __('pdf.receipt.org') }}</div>
        </div>

        <div class="title">{{ __('pdf.receipt.title') }}</div>

        <table>
            <tr><td class="label">{{ __('pdf.receipt.application_no') }}</td><td class="value">{{ $application->application_no }}</td></tr>
            <tr><td class="label">{{ __('pdf.receipt.applicant_name') }}</td><td class="value">{{ $application->full_name }}</td></tr>
            <tr><td class="label">{{ __('pdf.receipt.ic_no') }}</td><td class="value">{{ $application->ic_no }}</td></tr>
            <tr><td class="label">{{ __('pdf.receipt.assistance_type') }}</td><td class="value">{{ $application->assistance_type ?: __('pdf.receipt.assistance_default') }}</td></tr>
            <tr><td class="label">{{ __('pdf.receipt.submitted_at') }}</td><td class="value">{{ $application->submitted_at?->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</td></tr>
            <tr><td class="label">{{ __('pdf.receipt.status') }}</td><td class="value">
                {{ match($application->status) {
                    'submitted' => __('applications.status.submitted'),
                    'under_review' => __('applications.status.under_review'),
                    'approved' => __('applications.status.approved'),
                    'rejected' => __('applications.status.rejected'),
                    default => ucfirst($application->status),
                } }}
            </td></tr>
        </table>

        <div class="divider"></div>
        <p style="font-size: 12px; color: #64748B;">
            {{ __('pdf.receipt.note') }}
        </p>

        <div class="footer">
            <p>{{ __('pdf.receipt.generated_at', ['datetime' => $generated_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A')]) }} · <span class="mark">{{ __('pdf.receipt.confidential') }}</span></p>
            <p>{{ __('pdf.receipt.copyright', ['year' => date('Y')]) }}</p>
        </div>
    </div>
</body>
</html>
