<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Surat Kelulusan - {{ $profil->nama }}</title>
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
        $logoPath = storage_path('app/public/letter-assets/MAIJ.PNG');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
        $hijriDate = '';
        try {
            $timestamp = $tarikh->timestamp;
            $hijriMonths = ['Muharram','Safar','Rabiulawal','Rabiulakhir','Jamadilawal','Jamadilakhir','Rejab','Syaaban','Ramadhan','Syawal','Zulkaedah','Zulhijjah'];
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
                    JABATAN AGAMA ISLAM NEGERI JOHOR,
                </div>
                <div class="org-details">
                    <strong>BAHAGIAN PENDIDIKAN ISLAM,</strong><br>
                    Tingkat 3 & 4, Blok B,<br>
                    Pusat Islam Iskandar Johor,<br>
                    Jalan Masjid Abu Bakar,<br>
                    80990 Johor Bahru, Johor
                </div>
            </td>
            <td class="contact-cell">
                No. Tel : 07-228 2928/30/32/33/55<br>
                Faks &nbsp;&nbsp;&nbsp;&nbsp; : 07-223 3178<br>
                Emel &nbsp;&nbsp;&nbsp; : bpai@johor.gov.my<br>
                Website : jainj.johor.gov.my
            </td>
        </tr>
    </table>

    {{-- Reference block --}}
    <div class="ref">
        <span class="ref-no">Ruj. Kami : {{ $ref_no }} (A)</span><br>
        Tarikh &nbsp;&nbsp;&nbsp;&nbsp;: {{ $tarikh->format('d F Y') }}<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $hijriDate }}
    </div>

    <div class="reply-note">* Sila nyatakan rujukan bilangan surat kami apabila menjawab</div>

    {{-- Recipient --}}
    <div class="recipient">
        <strong>Kepada :</strong><br>
        {{ $profil->nama }}<br>
        @if ($profil->alamat)
            {{ $profil->alamat }}
        @endif
    </div>

    <div class="salutation"><strong>Tuan,</strong></div>

    {{-- Subject --}}
    <div class="subject">{{ $type === 'approved' ? 'KELULUSAN' : 'PENOLAKAN' }} BANTUAN ZAKAT MAJLIS AGAMA ISLAM NEGERI JOHOR (MAIJ)</div>

    <p>Dengan hormatnya, saya merujuk kepada perkara di atas.</p>

    {{-- Numbered paragraph 2 --}}
    <p class="num"><strong>2. &nbsp;</strong>
        @if ($type === 'approved')
            Sukacita dimaklumkan bahawa permohonan tuan/puan telah <strong>DILULUSKAN</strong> oleh Mesyuarat Jawatankuasa Zakat MAIJ. Butiran permohonan adalah seperti berikut :-
        @else
            Dimaklumkan bahawa permohonan tuan/puan telah <strong>DITOLAK</strong> selepas semakan oleh Mesyuarat Jawatankuasa Zakat MAIJ. Butiran permohonan adalah seperti berikut :-
        @endif
    </p>

    {{-- Details table --}}
    <table class="details">
        <thead>
            <tr>
                <th class="bil">BIL</th>
                <th class="butiran">BUTIRAN</th>
                <th>MAKLUMAT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bil">1</td>
                <td class="butiran">Nama Pemohon</td>
                <td>{{ $profil->nama }}</td>
            </tr>
            <tr>
                <td class="bil">2</td>
                <td class="butiran">No. Kad Pengenalan</td>
                <td>{{ $profil->ic }}</td>
            </tr>
            @if ($profil->telefon)
            <tr>
                <td class="bil">3</td>
                <td class="butiran">No. Telefon</td>
                <td>{{ $profil->telefon }}</td>
            </tr>
            @endif
            <tr>
                <td class="bil">4</td>
                <td class="butiran">Bilangan Tanggungan</td>
                <td>{{ $profil->bilangan_tanggungan }} orang</td>
            </tr>
            <tr>
                <td class="bil">5</td>
                <td class="butiran">Pendapatan Bulanan</td>
                <td>RM {{ number_format((float) $profil->pendapatan, 2) }}</td>
            </tr>
            <tr>
                <td class="bil">6</td>
                <td class="butiran">Had Kifayah</td>
                <td>RM {{ number_format((float) $profil->had_kifayah, 2) }}</td>
            </tr>
            <tr>
                <td class="bil">7</td>
                <td class="butiran">Status Kelayakan</td>
                <td>{{ $profil->status_kelayakan }}</td>
            </tr>
            @if ($profil->ai_score)
            <tr>
                <td class="bil">8</td>
                <td class="butiran">Skor Penilaian AI</td>
                <td>{{ number_format((float) $profil->ai_score, 0) }}%</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Numbered paragraph 3 --}}
    <p class="num"><strong>3. &nbsp;</strong>
        @if ($type === 'approved')
            Sehubungan itu, pihak tuan/puan dikehendaki hadir ke Pejabat MAIJ dalam tempoh <strong>14 hari</strong> dari tarikh surat ini dengan membawa dokumen asal seperti berikut :-
        @else
            Pihak tuan/puan boleh mengemukakan rayuan atau permohonan baharu dengan melampirkan dokumen yang lebih lengkap. Untuk makluman lanjut, sila rujuk ketetapan berikut :-
        @endif
    </p>

    <ol class="alpha" type="i">
        <li>Kad Pengenalan (asal &amp; salinan);</li>
        <li>Slip gaji terkini (3 bulan);</li>
        <li>Surat / Notis kelulusan ini (cetakan).</li>
    </ol>

    {{-- Numbered paragraph 4 --}}
    <p class="num"><strong>4. &nbsp;</strong>Untuk sebarang pertanyaan, sila hubungi <strong>Bahagian Pengurusan Zakat MAIJ</strong> di talian <strong>07-228 2928</strong>.</p>

    <p>Sekian, terima kasih.</p>

    {{-- Tagline --}}
    <div class="tagline">"BERKHIDMAT UNTUK NEGARA"</div>

    {{-- Signature --}}
    <div class="signature">
        <p class="amanah">Saya yang menjalankan amanah,</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p class="name">({{ strtoupper($admin->name ?? 'MOHD ASNAWI BIN AMAT JUPRI') }})</p>
        <p class="designation">
            Penolong Pengarah,<br>
            Unit Pentadbiran,<br>
            Bahagian Pendidikan Islam,<br>
            Jabatan Agama Islam Negeri Johor
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span class="certs">SAY</span>
        <span class="certs">ISO 9001:2008</span>
        <span class="certs">SIRIM</span><br>
        <span class="cert-line">CERTIFIED TO ISO 9001:2008 &nbsp;&nbsp; CERT. NO: AR-406</span><br>
        <span class="cert-line" style="margin-top:6px;display:block;">s.k. Fail</span>
    </div>
</body>
</html>
