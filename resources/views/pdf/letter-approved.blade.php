<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdf.letter.meta_title_approved', ['name' => $profil->nama]) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1A1A1A;
            line-height: 1.5;
            padding: 28px 36px;
        }
        .header {
            width: 100%;
            margin-bottom: 0;
            border-bottom: 2px solid #1A1A1A;
            padding-bottom: 10px;
        }
        .header td { vertical-align: top; padding: 4px 8px; }
        .header .logo-cell { width: 80px; text-align: center; padding-right: 14px; }
        .header .logo-cell img { width: 70px; height: auto; }
        .header .org-name {
            font-size: 13px;
            font-weight: 700;
            color: #1A1A1A;
        }
        .header .org-name .arab {
            font-size: 15px;
            letter-spacing: 0.02em;
            direction: rtl;
            text-align: right;
        }
        .header .org-details {
            font-size: 9px;
            color: #333;
            line-height: 1.45;
        }
        .header .contact-cell {
            width: 150px;
            font-size: 8px;
            color: #333;
            line-height: 1.5;
            text-align: right;
        }
        .ref {
            text-align: right;
            margin: 10px 0 6px 0;
            font-size: 10px;
            line-height: 1.5;
        }
        .ref .ref-no { font-weight: 700; }
        .reply-note {
            font-style: italic;
            font-size: 9px;
            margin-bottom: 14px;
            color: #555;
        }
        .recipient {
            margin-bottom: 14px;
            font-size: 10px;
            line-height: 1.45;
        }
        .salutation {
            margin-bottom: 10px;
            font-size: 10px;
        }
        .subject {
            font-weight: 700;
            font-size: 11px;
            text-align: center;
            margin: 14px 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        p {
            margin: 0 0 8px 0;
            font-size: 10px;
            text-align: justify;
        }
        p.num {
            margin-left: 16px;
            text-indent: -16px;
            padding-left: 16px;
        }
        table.details {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9px;
        }
        table.details th, table.details td {
            border: 0.5px solid #1A1A1A;
            padding: 4px 8px;
            vertical-align: top;
        }
        table.details th {
            text-align: center;
            font-weight: 700;
            background-color: #E8E8E8;
        }
        table.details td.bil { width: 30px; text-align: center; }
        table.details td.butiran { width: 180px; }
        ol.alpha {
            margin: 4px 0 8px 24px;
            font-size: 10px;
            line-height: 1.6;
        }
        ol.alpha li { margin-bottom: 2px; }
        .tagline {
            text-align: right;
            font-style: italic;
            font-size: 9px;
            margin: 20px 0 8px 0;
        }
        .signature {
            text-align: right;
            margin-top: 6px;
            font-size: 10px;
            line-height: 1.5;
        }
        .signature .amanah { font-size: 9px; margin-bottom: 28px; }
        .signature .name { font-weight: 700; text-transform: uppercase; }
        .signature .designation { font-size: 9px; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #999;
            font-size: 8px;
            color: #666;
            text-align: center;
            line-height: 1.4;
        }
        .footer .certs {
            display: inline-block;
            margin-right: 10px;
            font-weight: 700;
        }
        .footer .cert-line {
            margin-top: 2px;
        }
    </style>
</head>
<body>
    @php
        $statusKey = 'common.status.'.str_replace([' ', '-'], '_', strtolower((string) $profil->status_kelayakan));
        $statusLabel = trans()->has($statusKey) ? __($statusKey) : $profil->status_kelayakan;
        $logoPath = storage_path('app/public/letter-assets/MAIJ.PNG');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
        $hijriDate = '';
        try {
            $timestamp = $tarikh->timestamp;
            $hijriMonths = __('pdf.letter.hijri_months');
            $jd = (int)($timestamp / 86400) + 2440587.5;
            $l = $jd - 1948440 + 10632;
            $n = (int)(($l - 1) / 10631);
            $l = $l - 10631 * $n + 354;
            $j = ((int)((10985 - $l) / 5316)) * ((int)((50 * $l) / 17719)) + ((int)($l / 5670)) * ((int)((43 * $l) / 15238));
            $l = $l - ((int)((30 - $j) / 15)) * ((int)((17719 * $j) / 50)) - ((int)($j / 16)) * ((int)((15238 * $j) / 43)) + 29;
            $m = (int)((24 * $l) / 709);
            $d = $l - (int)((709 * $m) / 24);
            $y = 30 * $n + $j - 30;
            $hijriDate = $d . ' ' . $hijriMonths[$m - 1] . ' ' . $y . 'H';
        } catch (\Throwable $e) {
            $hijriDate = '';
        }
    @endphp

    {{-- Header --}}
    <table class="header">
        <tr>
            <td class="logo-cell">
                @if ($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" width="70">
                @endif
            </td>
            <td>
                <div class="org-name">
                    <div class="arab" style="font-size:16px;direction:rtl;text-align:right;">جابتن اݢام إسلام نڬري جوهر</div>
                    {{ __('pdf.letter.org_name') }}
                </div>
                <div class="org-details">
                    <strong>{{ __('pdf.letter.division') }}</strong><br>
                    {{ __('pdf.letter.address_line_1') }}<br>
                    {{ __('pdf.letter.address_line_2') }}<br>
                    {{ __('pdf.letter.address_line_3') }}<br>
                    {{ __('pdf.letter.address_line_4') }}
                </div>
            </td>
            <td class="contact-cell">
                {{ __('pdf.letter.tel') }} : 07-228 2928/30/32/33/55<br>
                {{ __('pdf.letter.fax') }} &nbsp;&nbsp;&nbsp;&nbsp; : 07-223 3178<br>
                {{ __('pdf.letter.email') }} &nbsp;&nbsp;&nbsp; : bpai@johor.gov.my<br>
                {{ __('pdf.letter.website') }} : jainj.johor.gov.my
            </td>
        </tr>
    </table>

    {{-- Reference block --}}
    <div class="ref">
        <span class="ref-no">{{ __('pdf.letter.our_ref') }} : {{ $ref_no }} (A)</span><br>
        {{ __('pdf.letter.date') }} &nbsp;&nbsp;&nbsp;&nbsp;: {{ $tarikh->locale(app()->getLocale())->translatedFormat('d F Y') }}<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $hijriDate }}
    </div>

    <div class="reply-note">{{ __('pdf.letter.reply_note') }}</div>

    {{-- Recipient --}}
    <div class="recipient">
        <strong>{{ __('pdf.letter.to') }}</strong><br>
        {{ $profil->nama }}<br>
        @if ($profil->alamat)
            {{ $profil->alamat }}
        @endif
    </div>

    <div class="salutation"><strong>{{ __('pdf.letter.salutation') }}</strong></div>

    {{-- Subject --}}
    <div class="subject">{{ $type === 'approved' ? __('pdf.letter.subject_approved') : __('pdf.letter.subject_rejected') }}</div>

    <p>{{ __('pdf.letter.intro') }}</p>

    {{-- Numbered paragraph 2 --}}
    <p class="num"><strong>2. &nbsp;</strong>
        @if ($type === 'approved')
            {!! __('pdf.letter.approved_paragraph') !!}
        @else
            {!! __('pdf.letter.rejected_paragraph') !!}
        @endif
    </p>

    {{-- Details table --}}
    <table class="details">
        <thead>
            <tr>
                <th class="bil">{{ __('pdf.letter.table_no') }}</th>
                <th class="butiran">{{ __('pdf.letter.table_details') }}</th>
                <th>{{ __('pdf.letter.table_info') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bil">1</td>
                <td class="butiran">{{ __('pdf.letter.name') }}</td>
                <td>{{ $profil->nama }}</td>
            </tr>
            <tr>
                <td class="bil">2</td>
                <td class="butiran">{{ __('pdf.letter.ic_no') }}</td>
                <td>{{ $profil->ic }}</td>
            </tr>
            @if ($profil->telefon)
            <tr>
                <td class="bil">3</td>
                <td class="butiran">{{ __('pdf.letter.phone') }}</td>
                <td>{{ $profil->telefon }}</td>
            </tr>
            @endif
            <tr>
                <td class="bil">4</td>
                <td class="butiran">{{ __('pdf.letter.dependents') }}</td>
                <td>{{ __('pdf.letter.dependents_unit', ['count' => $profil->bilangan_tanggungan]) }}</td>
            </tr>
            <tr>
                <td class="bil">5</td>
                <td class="butiran">{{ __('pdf.letter.monthly_income') }}</td>
                <td>RM {{ number_format((float) $profil->pendapatan, 2) }}</td>
            </tr>
            <tr>
                <td class="bil">6</td>
                <td class="butiran">{{ __('pdf.letter.kifayah') }}</td>
                <td>RM {{ number_format((float) $profil->had_kifayah, 2) }}</td>
            </tr>
            <tr>
                <td class="bil">7</td>
                <td class="butiran">{{ __('pdf.letter.eligibility_status') }}</td>
                <td>{{ $statusLabel }}</td>
            </tr>
            @if ($profil->ai_score)
            <tr>
                <td class="bil">8</td>
                <td class="butiran">{{ __('pdf.letter.ai_score') }}</td>
                <td>{{ number_format((float) $profil->ai_score, 0) }}%</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Numbered paragraph 3 --}}
    <p class="num"><strong>3. &nbsp;</strong>
        @if ($type === 'approved')
            {!! __('pdf.letter.approved_instruction') !!}
        @else
            {!! __('pdf.letter.rejected_instruction') !!}
        @endif
    </p>

    <ol class="alpha" type="i">
        @foreach ($type === 'approved' ? __('pdf.letter.approved_documents') : __('pdf.letter.rejected_documents') as $document)
        <li>{{ $document }}</li>
        @endforeach
    </ol>

    {{-- Numbered paragraph 4 --}}
    <p class="num"><strong>4. &nbsp;</strong>{!! __('pdf.letter.contact', ['phone' => '07-228 2928']) !!}</p>

    <p>{{ __('pdf.letter.closing') }}</p>

    {{-- Tagline --}}
    <div class="tagline">{{ __('pdf.letter.tagline') }}</div>

    {{-- Signature --}}
    <div class="signature">
        <p class="amanah">{{ __('pdf.letter.amanah') }}</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p class="name">({{ strtoupper($admin->name ?? 'MOHD ASNAWI BIN AMAT JUPRI') }})</p>
        <p class="designation">
            @foreach (__('pdf.letter.designation') as $line)
                {{ $line }}@if (! $loop->last)<br>@endif
            @endforeach
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span class="certs">SAY</span>
        <span class="certs">ISO 9001:2008</span>
        <span class="certs">SIRIM</span><br>
        <span class="cert-line">CERTIFIED TO ISO 9001:2008 &nbsp;&nbsp; CERT. NO: AR-406</span><br>
        <span class="cert-line" style="margin-top:6px;display:block;">{{ __('pdf.letter.cc') }}</span>
    </div>
</body>
</html>
