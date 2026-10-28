<?php

return [
    'household' => [
        'title' => 'Isi Rumah Saya',
        'subtitle' => 'Senarai ahli isi rumah yang berkaitan dengan No. Kad Pengenalan anda.',

        'empty' => [
            'title' => 'Tiada rekod isi rumah',
            'description' => 'Maklumat isi rumah akan muncul selepas permohonan anda diproses atau anda mengimbas Borang Bantuan MAIJ.',
            'action' => 'Mohon Bantuan',
        ],

        'summary' => [
            'members' => 'Jumlah Ahli',
            'dependants' => 'Jumlah Tanggungan',
            'income' => 'Pendapatan Isi Rumah',
            'commitments' => 'Komitmen Bulanan',
        ],

        'head' => [
            'title' => 'Ketua Keluarga',
            'description' => 'Ketua isi rumah',
        ],

        'labels' => [
            'name' => 'Nama',
            'ic' => 'No. IC',
            'income' => 'Pendapatan',
            'age' => 'Umur',
            'occupation' => 'Pekerjaan',
            'health' => 'Kesihatan',
            'active_assistance' => 'Bantuan aktif',
            'years' => ':age tahun',
        ],

        'members' => [
            'title' => 'Ahli Isi Rumah',
            'description' => ':count ahli keseluruhan',
            'dependant' => 'Tanggungan',
            'student' => 'Pelajar',
            'empty_title' => 'Tiada ahli direkodkan',
            'empty_description' => 'Ahli isi rumah akan muncul di sini.',
        ],
    ],

    'finance' => [
        'title' => 'Kewangan Saya',
        'subtitle' => 'Ringkasan kewangan isi rumah anda.',

        'empty' => [
            'title' => 'Tiada maklumat kewangan',
            'description' => 'Maklumat kewangan isi rumah akan muncul selepas permohonan anda diproses.',
        ],

        'summary' => [
            'income' => 'Jumlah Pendapatan Isi Rumah',
            'commitments' => 'Jumlah Komitmen',
            'dependants' => 'Bilangan Tanggungan',
        ],

        'income' => [
            'title' => 'Pendapatan',
            'description' => 'Sumber pendapatan isi rumah',
            'applicant' => 'Pendapatan Pemohon',
            'spouse' => 'Pendapatan Pasangan',
            'other' => 'Pendapatan Ahli Lain',
            'total' => 'Jumlah Pendapatan',
        ],

        'commitments' => [
            'title' => 'Komitmen Bulanan',
            'description' => 'Perbelanjaan tetap isi rumah',
            'recorded' => 'Jumlah komitmen bulanan direkodkan',
        ],

        'balance' => [
            'label' => 'Baki Pendapatan Selepas Komitmen',
            'surplus' => 'Lebihan',
            'deficit' => 'Defisit',
        ],
    ],

    'assistance' => [
        'title' => 'Bantuan Saya',
        'subtitle' => 'Semua bantuan yang diterima oleh ahli isi rumah anda.',

        'empty' => [
            'title' => 'Tiada rekod bantuan',
            'description' => 'Bantuan yang diterima oleh isi rumah anda akan dipaparkan di sini selepas kelulusan MAIJ.',
            'recorded_title' => 'Tiada bantuan direkodkan',
            'recorded_description' => 'Bantuan yang diluluskan akan dipaparkan di sini.',
            'household_description' => 'Bantuan yang diluluskan untuk isi rumah anda akan dipaparkan di sini.',
        ],

        'totals' => [
            'monthly' => 'Bantuan Bulanan',
            'one_off' => 'Bantuan One-Off',
            'total' => 'Jumlah Keseluruhan',
        ],

        'per_month' => '/ bulan',
        'reference' => 'No. Rujukan: :reference',
    ],

    'hadkifayah' => [
        'title' => 'Kalkulator Had Kifayah',
        'subtitle' => 'Kira anggaran had kifayah berdasarkan maklumat isi rumah anda.',

        'household_info' => 'Maklumat Isi Rumah',

        'fields' => [
            'applicant_income' => 'Pendapatan Pemohon (RM)',
            'spouse_income' => 'Pendapatan Pasangan (RM)',
            'dependants' => 'Bilangan Tanggungan Isi Rumah',
            'n_isteri_suami' => 'Isteri / Suami',
            'n_ibu_bapa' => 'Ibu Bapa',
            'n_dewasa_tidak_bekerja' => 'Dewasa Tdk Bekerja',
            'n_dewasa_bekerja_tanggung' => 'Dewasa Bekerja Ditanggung',
            'n_ipt' => 'IPT',
            'n_remaja' => 'Remaja',
            'n_kanak_rendah' => 'Kanak Rendah',
            'n_kanak_tadika' => 'Kanak Tadika',
            'monthly_expenses' => 'Perbelanjaan Bulanan',
            'housing_type' => 'Jenis Perumahan',
            'own_home' => 'Rumah Sendiri',
            'rented_home' => 'Sewa',
            'utilities' => 'Utiliti (RM)',
            'transport' => 'Pengangkutan (RM)',
            'medical' => 'Perubatan (RM)',
        ],

        'calculate' => 'Kira Had Kifayah',
        'reset' => 'Set Semula',

        'result' => [
            'title' => 'Keputusan',
            'status_label' => 'Status Kelayakan',
            'confidence' => 'Keyakinan: :value%',
            'income_total' => 'Jumlah Pendapatan',
            'had_kifayah' => 'Had Kifayah',
            'balance' => 'Baki',
            'reasoning' => 'Justifikasi',
            'empty_hint' => 'Isi maklumat di sebelah dan klik',
        ],
    ],
];
