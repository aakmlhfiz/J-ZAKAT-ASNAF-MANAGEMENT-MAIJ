<?php

return [
    'module' => 'Semakan Audit',
    'module_subtitle' => 'Ruang kerja semakan audit dalaman MAIJ untuk profil asnaf & isi rumah',

    'status' => [
        'draft' => 'Draf',
        'in_progress' => 'Sedang Diproses',
        'pending_review' => 'Menunggu Semakan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ],

    'decision' => [
        'compliant' => 'Patuh',
        'minor_issues' => 'Isu Kecil',
        'major_issues' => 'Isu Besar',
        'escalate' => 'Eskalasi',
    ],

    'review_status' => [
        'pending' => 'Menunggu',
        'approved' => 'Diluluskan',
        'rejected' => 'Ditolak',
    ],

    'severity' => [
        'critical' => 'Kritikal',
        'high' => 'Tinggi',
        'medium' => 'Sederhana',
        'low' => 'Rendah',
    ],

    'resolution' => [
        'false_positive' => 'Positif Palsu',
        'acknowledged' => 'Diakui',
        'escalated' => 'Dieskalasi',
        'resolved' => 'Diselesaikan',
    ],

    'risk' => [
        'critical' => 'Kritikal',
        'high' => 'Tinggi',
        'medium' => 'Sederhana',
        'low' => 'Rendah',
    ],

    'step' => [
        'income_check' => 'Semak Pendapatan Isi Rumah',
        'kifayah_compare' => 'Bandingkan dengan Had Kifayah',
        'commitments_check' => 'Semak Komitmen Utama',
        'assistance_check' => 'Semak Bantuan Diterima',
        'anomaly_check' => 'Kenal Pasti Anomali',
        'record_action' => 'Rekod Tindakan Pegawai',
    ],

    'flag' => [
        'INCOME_SPIKE' => 'Pendapatan Tidak Biasa Tinggi',
        'DUPLICATE_IC' => 'Nombor Kad Pengenalan Berganda',
        'ASSISTANCE_OVERLOAD' => 'Beban Bantuan Aktif Berlebihan',
        'EXPIRED_ASSISTANCE' => 'Bantuan Aktif Tamat Tempoh',
        'HIGH_RENT_RATIO' => 'Nisbah Sewa Tinggi',
        'INCOME_NEAR_KIFAYAH' => 'Pendapatan Hampir Garis Kifayah',
        'MISSING_DOCUMENT' => 'Bantuan Tanpa Rujukan Dokumen',
        'OFFICER_FREQUENCY' => 'Kekerapan Kelulusan Pegawai Tinggi',
    ],

    'four_eyes' => 'Penyemak tidak boleh sama dengan pegawai audit (prinsip 4 mata).',
    'overdue_days' => 'Kes audit telah melebihi :days hari tanpa diselesaikan.',

    'category' => [
        'auth' => 'Pengesahan',
        'application' => 'Permohonan',
        'data' => 'Data',
        'security' => 'Keselamatan',
        'system' => 'Sistem',
        'money' => 'Kewangan',
        'audit' => 'Semakan Audit',
    ],

    'role' => [
        'admin' => 'Pentadbir',
        'portal' => 'Pemohon',
        'system' => 'Sistem',
    ],

    'logs' => [
        'title' => 'Audit Log',
        'subtitle' => 'Rekod aktiviti sistem J-ZAKAT',
        'eyebrow' => 'Pentadbiran',
        'export_csv' => 'Eksport CSV',

        'stats' => [
            'total' => 'Jumlah Rekod',
            'today' => 'Aktiviti Hari Ini',
            'logins_today' => 'Log Masuk Hari Ini',
            'unique_users' => 'Pengguna Unik Hari Ini',
        ],

        'filter' => [
            'search' => 'Cari',
            'search_placeholder' => 'Emel, IP, aksi...',
            'user' => 'Pengguna',
            'role' => 'Peranan',
            'category' => 'Kategori',
            'action' => 'Aksi',
            'from' => 'Dari',
            'to' => 'Hingga',
            'all' => 'Semua',
            'apply' => 'Tapis',
            'reset' => 'Reset',
        ],

        'columns' => [
            'datetime' => 'Tarikh / Masa',
            'user' => 'Pengguna',
            'category' => 'Kategori',
            'action' => 'Aksi',
            'description' => 'Keterangan',
            'ip' => 'IP',
            'browser_os' => 'Pelayar · OS',
            'device' => 'Peranti',
        ],

        'view_metadata' => 'Lihat metadata',
        'empty' => 'Tiada rekod audit log dijumpai.',
    ],

    'logger' => [
        'login_success' => 'Log masuk berjaya',
        'login_failed' => 'Percubaan log masuk gagal',
        'logout' => 'Log keluar',
        'password_changed' => 'Kata laluan akaun ditukar',
        'password_reset' => 'Kata laluan ditetapkan semula melalui emel',
        'profile_updated' => 'Profil dikemaskini',
        'application_created' => 'Profil asnaf baharu dicipta: :name (:ic)',
        'application_updated' => 'Profil asnaf dikemaskini: :name (:ic)',
        'application_approved' => 'Permohonan diluluskan: :name (:ic)',
        'application_rejected' => 'Permohonan ditolak: :name (:ic)',
        'application_submitted' => 'Permohonan baharu dihantar oleh pemohon: :name',
        'documents_requested' => 'Dokumen tambahan diminta: :name (:ic)',
        'csv_imported' => 'Import CSV: :success berjaya, :failed gagal (:total jumlah)',
        'pdf_imported' => 'Import PDF/OCR: :file',
        'pdf_committed' => 'Data PDF/OCR disahkan: :file → :name',
        'borang_scan_uploaded' => 'Imbas Borang Bantuan MAIJ dimuat naik: :file',
        'borang_scan_committed' => 'Borang Bantuan MAIJ disimpan & dihantar: :name (:ic)',
        'document_uploaded' => 'Dokumen dimuat naik: :file',
        'ai_assessment' => 'Penilaian AI dijalankan: :name (:ic)',
        'money_created' => 'Transaksi bantuan direkod: :type RM :amount',
        'money_updated' => 'Transaksi bantuan dikemaskini: :type RM :amount',
        'money_deleted' => 'Transaksi bantuan dipadam: :type RM :amount',
    ],

    'activity' => [
        'login' => 'Log masuk berjaya',
        'logout' => 'Log keluar',
        'profile_updated' => 'Profil dikemaskini',
        'password_changed' => 'Kata laluan ditukar',
        'photo_uploaded' => 'Gambar profil dikemaskini',
        'settings_updated' => 'Tetapan :key dikemaskini',
        'two_factor_enabled' => 'Pengesahan dua faktor diaktifkan',
        'two_factor_disabled' => 'Pengesahan dua faktor dinyahaktifkan',
        'account_password_changed' => 'Kata laluan akaun telah diubah',
    ],

    'export' => [
        'id' => 'ID',
        'created_at' => 'Tarikh Dicipta',
        'user_id' => 'ID Pengguna',
        'user_role' => 'Peranan Pengguna',
        'user_email' => 'Emel Pengguna',
        'category' => 'Kategori',
        'action' => 'Aksi',
        'description' => 'Keterangan',
        'ip_address' => 'Alamat IP',
        'browser' => 'Pelayar',
        'device' => 'Peranti',
        'os' => 'OS',
        'metadata' => 'Metadata',
    ],
];
