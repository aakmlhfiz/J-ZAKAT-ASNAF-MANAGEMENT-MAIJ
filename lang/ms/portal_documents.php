<?php

return [
    'documents' => [
        'title' => 'Dokumen Saya',
        'subtitle' => 'Muat naik dan urus dokumen sokongan permohonan anda.',

        'upload' => [
            'title' => 'Muat Naik Dokumen',
            'description' => 'Format PDF, JPG atau PNG. Maksimum 10MB.',
            'type' => 'Jenis Dokumen',
            'file' => 'Fail',
            'submit' => 'Muat Naik',
            'optional' => 'Pilihan',
            'uploading' => 'Memuat naik...',
        ],

        'types' => [
            'ic' => 'Salinan Kad Pengenalan',
            'salary' => 'Slip Gaji / Penyata Pendapatan',
            'supporting' => 'Dokumen Sokongan',
            'other' => 'Lain-lain',
        ],

        'empty' => [
            'title' => 'Tiada dokumen',
            'description' => 'Muat naik dokumen sokongan anda menggunakan borang di atas.',
        ],

        'status' => [
            'uploaded' => 'Dimuat Naik',
            'processing' => 'Memproses',
            'extracted' => 'Teks Diekstrak',
            'needs_review' => 'Menunggu Pengesahan',
            'committed' => 'Disahkan',
            'failed' => 'Gagal',
            'rejected' => 'Ditolak',
        ],

        'fallback_type' => 'Dokumen Sokongan',
        'ocr_confidence' => 'Keyakinan OCR: :value%',
        'download' => 'Muat Turun',
        'review' => 'Semak',
        'reprocess' => 'Proses Semula',
    ],

    'letters' => [
        'title' => 'Surat & Resit',
        'subtitle' => 'Muat turun surat rasmi dan resit permohonan anda.',

        'empty' => [
            'title' => 'Tiada surat atau resit',
            'description' => 'Surat kelulusan, notis penolakan dan resit permohonan akan dipaparkan di sini.',
        ],

        'fallback_assistance' => 'Permohonan Bantuan',
        'approved_letter' => 'Surat Kelulusan',
        'rejection_notice' => 'Notis Penolakan',
        'receipt' => 'Resit Permohonan',

        'status' => [
            'approved' => 'Diluluskan',
            'rejected' => 'Ditolak',
            'under_review' => 'Dalam Semakan',
            'submitted' => 'Dihantar',
        ],
    ],

    'flash' => [
        'uploaded' => 'Dokumen berjaya dimuat naik.',
        'duplicate' => 'Dokumen yang sama telah dimuat naik sebelum ini. Anda dibawa ke rekod sedia ada.',
        'file_not_found' => 'Fail tidak dijumpai pada storan.',
        'reprocess_only_borang' => 'Hanya Borang Bantuan MAIJ boleh diproses semula.',
        'already_committed' => 'Rekod ini telah dihantar — tidak perlu diproses semula.',
        'reprocessing' => 'Dokumen sedang diproses semula.',
    ],

    'notifications' => [
        'uploaded_title' => 'Dokumen Dimuat Naik',
        'uploaded_body' => 'Dokumen anda telah berjaya dimuat naik.',
    ],
];
