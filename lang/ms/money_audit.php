<?php

return [
    'title' => 'Audit Kewangan',
    'subtitle' => 'Pengauditan transaksi bantuan & pergerakan wang J-ZAKAT',
    'eyebrow' => 'Pentadbiran',
    'export_csv' => 'Eksport CSV',

    'stats' => [
        'total_approved' => 'Jumlah Diluluskan (RM)',
        'records' => 'Bilangan Rekod',
        'this_month' => 'Bulan Ini (RM)',
        'households' => 'Isi Rumah Terlibat',
    ],

    'records_count' => ':count rekod',

    'tabs' => [
        'transactions' => 'Transaksi',
        'trail' => 'Jejak Audit',
    ],

    'filter' => [
        'search' => 'Cari',
        'search_placeholder' => 'Rujukan, pegawai, ahli, isi rumah...',
        'trail_search_placeholder' => 'Emel, IP, aksi...',
        'user' => 'Pengguna',
        'action' => 'Aksi',
        'jenis_bantuan' => 'Jenis Bantuan',
        'status' => 'Status',
        'frequency' => 'Kekerapan',
        'from' => 'Dari',
        'to' => 'Hingga',
        'all' => 'Semua',
        'apply' => 'Tapis',
        'reset' => 'Reset',
    ],

    'status' => [
        'approved' => 'Diluluskan',
        'pending' => 'Dalam Proses',
        'rejected' => 'Ditolak',
    ],

    'frequency' => [
        'one_off' => 'Sekali (One-off)',
        'monthly' => 'Bulanan',
    ],

    'columns' => [
        'datetime' => 'Tarikh / Masa',
        'user' => 'Pengguna',
        'action' => 'Aksi',
        'description' => 'Keterangan',
        'metadata' => 'Metadata',
        'ip' => 'IP',
        'approved_date' => 'Tarikh Lulus',
        'household' => 'Isi Rumah',
        'member' => 'Ahli',
        'jenis_bantuan' => 'Jenis Bantuan',
        'amount' => 'Amaun',
        'status' => 'Status',
        'officer' => 'Pegawai',
        'reference' => 'No Rujukan',
    ],

    'per_month_short' => '/bln',

    'metadata' => [
        'type' => 'Jenis',
        'amount' => 'Amaun',
        'reference' => 'Rujukan',
        'view_changes' => 'Lihat perubahan',
    ],

    'empty_transactions' => 'Tiada transaksi bantuan dijumpai.',
    'empty_trail' => 'Tiada jejak audit kewangan dijumpai.',

    'export' => [
        'id' => 'ID',
        'approved_date' => 'Tarikh Lulus',
        'household' => 'Isi Rumah',
        'member' => 'Ahli',
        'jenis_bantuan' => 'Jenis Bantuan',
        'amount' => 'Amaun (RM)',
        'frequency' => 'Kekerapan',
        'status' => 'Status',
        'approval_officer' => 'Pegawai Kelulusan',
        'reference' => 'No Rujukan',
        'description' => 'Keterangan',
    ],
];
