<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Teks Dijana oleh Perkhidmatan
    |--------------------------------------------------------------------------
    |
    | Teks paparan yang dijana oleh perkhidmatan aplikasi, seperti cadangan
    | dan sebab penilaian Had Al-Kifayah. Nilai pengiraan dan kod status
    | tidak diterjemah.
    |
    */

    'hadkifayah' => [
        'recommendation' => [
            'eligible' => 'Pemohon disyorkan layak menerima bantuan zakat.',
            'not_eligible' => 'Pendapatan melebihi garis Had Kifayah. Pemohon belum layak pada masa ini.',
        ],

        'reasoning' => [
            'income_below' => '✓ Pendapatan isi rumah (RM :income) lebih rendah daripada Had Kifayah (RM :threshold)',
            'income_not_below' => '✗ Pendapatan isi rumah (RM :income) melebihi atau sama dengan Had Kifayah (RM :threshold)',
            'dependants_high' => '✓ Bilangan tanggungan tinggi (:count orang)',
            'dependants_low' => '○ Bilangan tanggungan sederhana/rendah (:count orang)',
            'gap_large' => '✓ Jurang keperluan besar (-:gap)',
            'gap_small' => '○ Jurang keperluan kecil (-:gap)',
            'expenses_recorded' => '✓ Perbelanjaan tetap direkodkan (RM :amount)',
            'expenses_none' => '○ Tiada perbelanjaan tetap direkodkan',
        ],

        'breakdown' => [
            'head' => 'Ketua Keluarga',
            'spouse' => 'Isteri / Suami × :count',
            'parents' => 'Ibu Bapa Tanggungan × :count',
            'adult_unemployed' => 'Dewasa Tidak Bekerja × :count',
            'adult_working_dependant' => 'Dewasa Bekerja Ditanggung × :count',
            'higher_education' => 'IPT × :count',
            'teenager' => 'Remaja × :count',
            'primary_school' => 'Sekolah Rendah × :count',
            'kindergarten' => 'Tadika × :count',
            'rented_selected' => 'Rumah Sewa (dipilih)',
            'rented_not_selected' => 'Rumah Sewa (tidak dipilih)',
            'utilities' => 'Utiliti (input)',
            'transport' => 'Pengangkutan (input)',
            'medical' => 'Perubatan (input)',
        ],
    ],

];
