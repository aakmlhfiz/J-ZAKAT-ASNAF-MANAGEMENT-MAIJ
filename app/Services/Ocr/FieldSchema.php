<?php

namespace App\Services\Ocr;

class FieldSchema
{
    public static array $labels = [
        // --- Applicant Info ---
        'nama' => [
            'Nama',
            'Nama Penuh',
            'Nama Pemohon',
            'Nama Pemohan',
            'Name',
            'Full Name',
            'Applicant Name',
            'Nama Penuh Pemohon',
        ],
        'ic' => [
            'No IC',
            'No. IC',
            'No Kad Pengenalan',
            'No. Kad Pengenalan',
            'NRIC',
            'NRIC No',
            'NRIC No.',
            'Identification Number',
            'IC Number',
            'IC No',
            'IC No.',
            'No. KP',
            'No. K/P',
            'No KP',
            'No K/P',
            'MyKad',
            'MyKad No',
        ],
        'ic_ketua_rumah' => [
            'IC Ketua Rumah',
            'IC Ketua Isi Rumah',
            'No IC Ketua Rumah',
            'No IC Ketua Isi Rumah',
            'Head of Family IC',
            'Head of Household IC',
            'IC Suami',
            'IC Isteri',
            'Spouse IC',
            'No. K/P',
        ],
        'hubungan' => [
            'Hubungan',
            'Hubungan Dengan Ketua',
            'Relationship',
            'Relationship with Head',
            'Status Hubungan',
        ],
        'status_perkahwinan' => [
            'Status Perkahwinan',
            'Status:',
            'Taraf Perkahwinan',
            'Marital Status',
            'Berkahwin',
            'Kahwin',
        ],
        'merokok' => [
            'Merokok',
            'Smoking',
            'Perokok',
        ],

        // --- Contact ---
        'telefon' => [
            'Telefon',
            'No Telefon',
            'No. Telefon',
            'No Tel',
            'No. Tel',
            'Tel',
            'Phone',
            'Phone Number',
        ],
        'no_telefon_bimbit' => [
            'No Telefon Bimbit',
            'No. Telefon Bimbit',
            'Telefon Bimbit',
            'No. HP',
            'Mobile',
            'Mobile Number',
            'Mobile Phone',
            'Bimbit',
        ],
        'no_telefon_rumah' => [
            'No Telefon Rumah',
            'No. Telefon Rumah',
            'Telefon Rumah',
            'Home Phone',
            'Home Telephone',
            'Rumah',
        ],

        // --- Address ---
        'alamat' => [
            'Alamat',
            'Alamat Penuh',
            'Alamat Terkini',
            'Alamat Rumah',
            'Alamat Surat Menyurat',
            'Mailing Address',
            'Address',
            'Full Address',
            'Home Address',
            'Current Address',
        ],
        'daerah' => [
            'Daerah',
            'District',
        ],
        'bandar' => [
            'Bandar',
            'City',
            'Town',
        ],
        'poskod' => [
            'Poskod',
            'Postcode',
            'Postal Code',
            'Zip Code',
        ],
        'dun' => [
            'DUN',
            'Dewan Undangan Negeri',
            'State Constituency',
        ],
        'parlimen' => [
            'Parlimen',
            'Parliament',
            'Parliamentary',
        ],

        // --- Health ---
        'kesihatan' => [
            'Kesihatan',
            'Status Kesihatan',
            'Keadaan Kesihatan',
            'Health',
            'Health Condition',
            'Health Status',
            'Penyakit',
            'Sakit',
        ],

        // --- Financial ---
        'pendapatan' => [
            'Pendapatan',
            'Pendapatan Bulanan',
            'Pendapatan Sebulan',
            'Pendapatan Pemohon',
            'Gaji',
            'Income',
            'Monthly Income',
            'Salary',
            'Monthly Salary',
        ],
        'pendapatan_pasangan' => [
            'Pendapatan Pasangan',
            'Pendapatan Suami',
            'Pendapatan Isteri',
            'Gaji Pasangan',
            'Spouse Income',
        ],
        'pendapatan_isi_rumah' => [
            'Pendapatan Isi Rumah',
            'Pendapatan Keluarga',
            'Jumlah Pendapatan Isi Rumah',
            'Total Household Income',
            'Household Income',
        ],
        'pendapatan_lain' => [
            'Pendapatan Lain',
            'Pendapatan Tambahan',
            'Lain-Lain Pendapatan',
            'Other Income',
            'Additional Income',
        ],
        'perbelanjaan' => [
            'Perbelanjaan',
            'Perbelanjaan Bulanan',
            'Jumlah Perbelanjaan',
            'Total Expenses',
            'Expenses',
            'Monthly Expenses',
        ],
        'sewa' => [
            'Sewa',
            'Sewa Rumah',
            'Sewa Bulanan',
            'Bayaran Sewa',
            'Rent',
            'Rental',
            'House Rent',
            'Monthly Rent',
        ],
        'utiliti' => [
            'Utiliti',
            'Bil Utiliti',
            'Utiliti Bulanan',
            'Utilities',
            'Utility Bills',
            'Monthly Utilities',
        ],
        'pengangkutan' => [
            'Pengangkutan',
            'Kos Pengangkutan',
            'Kenderaan',
            'Transport',
            'Transportation',
            'Transport Cost',
            'Vehicle',
        ],
        'perubatan' => [
            'Perubatan',
            'Kos Perubatan',
            'Perbelanjaan Perubatan',
            'Rawatan',
            'Medical',
            'Medical Expenses',
            'Medical Cost',
            'Treatment',
        ],
        'perumahan' => [
            'Status Perumahan',
            'Perumahan',
            'Jenis Perumahan',
            'Housing',
            'Housing Status',
        ],
        'bilangan_tanggungan' => [
            'Bilangan Tanggungan',
            'Tanggungan',
            'Jumlah Tanggungan',
            'Bilangan Anak',
            'Bilangan Ahli Keluarga',
            'Dependents',
            'No. of Dependents',
            'Number of Dependents',
        ],

        // --- Application / Bantuan ---
        'bantuan_sara_hidup' => [
            'Bantuan Sara Hidup',
            'Sara Hidup',
            'Bantuan Sara Hidup Bulanan',
            'Monthly Living Assistance',
        ],
        'bantuan_perubatan' => [
            'Bantuan Perubatan',
            'Bantuan Rawatan',
            'Medical Assistance',
            'Medical Aid',
        ],
        'bantuan_berdikari' => [
            'Bantuan Berdikari',
            'Bantuan Keusahawanan',
            'Self-Reliance Assistance',
            'Berdikari',
        ],
        'bantuan_perumahan' => [
            'Bantuan Perumahan',
            'Bantuan Rumah',
            'Bantuan Baik Pulih Rumah',
            'Housing Assistance',
            'Housing Aid',
        ],
        'menjelaskan_hutang' => [
            'Menjelaskan Hutang',
            'Bayaran Hutang',
            'Hutang',
            'Debt',
            'Debt Settlement',
            'Debt Repayment',
        ],
        'jumlah_bantuan_diperlukan' => [
            'Jumlah Bantuan Diperlukan',
            'Jumlah Bantuan',
            'Jumlah Diperlukan',
            'Amount Required',
            'Total Assistance',
            'Amount Needed',
        ],
        'sebab_memohon_bantuan' => [
            'Sebab Memohon Bantuan',
            'Sebab Memohon',
            'Alasan Memohon',
            'Tujuan Permohonan',
            'Reason for Application',
            'Reason',
            'Purpose',
        ],

        // --- Household Member Table (prefixed with isi_rumah_) ---
        'isi_rumah_nama' => [
            'Nama Ahli',
            'Nama Anggota',
            'Member Name',
            'Family Member Name',
        ],
        'isi_rumah_ic' => [
            'IC Ahli',
            'No IC Ahli',
            'Member IC',
            'Family Member IC',
        ],
        'isi_rumah_hubungan' => [
            'Hubungan Ahli',
            'Hubungan Anggota',
            'Member Relationship',
        ],
        'isi_rumah_umur' => [
            'Umur Ahli',
            'Umur Anggota',
            'Age',
            'Member Age',
        ],
        'isi_rumah_pekerjaan' => [
            'Pekerjaan Ahli',
            'Pekerjaan Anggota',
            'Occupation',
            'Member Occupation',
            'Job',
        ],
        'isi_rumah_pendapatan' => [
            'Pendapatan Ahli',
            'Pendapatan Anggota',
            'Income',
            'Member Income',
        ],
        'isi_rumah_pendidikan' => [
            'Pendidikan Ahli',
            'Tahap Pendidikan',
            'Education',
            'Member Education',
            'Education Level',
        ],
        'isi_rumah_kesihatan' => [
            'Kesihatan Ahli',
            'Status Kesihatan Ahli',
            'Health Condition',
            'Member Health',
        ],

        // --- Borang Bantuan MAIJ: Section 5A (Sara Hidup) ---
        'jumlah_sara_hidup' => [
            'JUM. Yang Diperlukan',
            'Jumlah Yang Diperlukan',
            'Jumlah Sara Hidup',
            'Jumlah Bantuan Sara Hidup',
        ],
        'no_akaun_bimb' => [
            'No. Akaun BIMB',
            'No Akaun BIMB',
            'No. Akaun Bank',
            'Nombor Akaun',
        ],
        'bantuan_agensi_lain_ada' => [
            'Bantuan daripada Agensi lain',
            'Bantuan Dari Agensi Lain',
            'Ada',
        ],
        'bantuan_agensi_lain_nyatakan' => [
            'Nyatakan',
            'Agensi Lain Nyatakan',
        ],
        'bantuan_agensi_lain_jumlah' => [
            'RM',
        ],
        'tidak_pernah_terima_bantuan' => [
            'Tidak pernah menerima bantuan dari agensi lain',
            'Tidak Pernah Menerima',
        ],

        // --- Borang Bantuan MAIJ: Section 5B (Perubatan) ---
        'perubatan_jenis_rawatan' => [
            'Dialisis',
            'Pembedahan',
            'Jenis Perubatan',
        ],
        'perubatan_pemohon_or_tanggungan' => [
            'Pemohon',
            'Tanggungan',
        ],
        'perubatan_nama_tanggungan' => [
            'Nama Tanggung',
            'Nama Tanggungan',
            'Nama Tanggunggan',
        ],
        'perubatan_hubungan_tanggungan' => [
            'Hubungan',
        ],
        'perubatan_no_kp_tanggungan' => [
            'No. Kad Pengenalan',
            'No K/P',
            'No Kad Pengenalan',
        ],
        'perubatan_jenis_penyakit' => [
            'Jenis Penyakit',
            'Penyakit',
        ],
        'perubatan_mula_mengidap' => [
            'Mula Mengidap',
            'Tarikh Mula',
        ],
        'perubatan_tempat_rawatan' => [
            'Tempat Rawatan',
            'Hospital',
            'Klinik',
        ],
        'perubatan_kos_rawatan' => [
            'Kos Rawatan',
            'Kos Rawatan : RM',
        ],
        'perubatan_harga_alatan' => [
            'Harga Alatan : RM',
            'Harga Alatan',
            'Harga Peralatan',
        ],

        // --- Borang Bantuan MAIJ: Section 5C (Berdikari) ---
        'jenis_berdikari' => [
            'Peralatan',
            'Modal',
            'Berdikari',
        ],
        'berdikari_jenis_peralatan' => [
            'Jenis peralatan yang dipohon',
            'Peralatan yang Dipohon',
        ],
        'berdikari_harga_peralatan' => [
            'Harga Peralatan/Modal',
            'Harga Peralatan',
            'Harga Modal',
        ],
        'berdikari_jenis_perniagaan' => [
            'Jenis Perniagaan',
        ],
        'berdikari_sebab_memerlukan' => [
            'Sebab memerlukan',
            'Sebab Memerlukan',
        ],
        'berdikari_pengalaman_ada' => [
            'Pengalaman Berniaga',
            'Ada',
            'Tiada',
        ],
        'berdikari_pengalaman_tahun' => [
            'berapa tahun',
            'Tempoh Pengalaman',
        ],

        // --- Borang Bantuan MAIJ: Section 5D (Perumahan) ---
        'jenis_bantuan_perumahan' => [
            'Bina Baru',
            'Baik Pulih',
        ],
        'status_tanah' => [
            'Status Tanah',
            'Sendiri',
            'Sewa',
            'Tumpang',
        ],
        'nama_dalam_geran' => [
            'Nama dalam Geran',
            'Nama Dalam Geran',
        ],
        'no_geran' => [
            'No. Geran',
            'No Geran',
            'Nombor Geran',
        ],
        'perumahan_laporan_polis' => [
            'Laporan Polis',
            'Ada',
            'Tiada',
        ],

        // --- Borang Bantuan MAIJ: Section 5E (Menjelaskan Hutang) ---
        'sebab_berhutang' => [
            'Sebab berhutang',
            'Sebab Berhutang',
        ],
        'jumlah_hutang' => [
            'Jumlah Hutang',
            'Jumlah Hutang : RM',
        ],
        'sebab_gagal_bayar_hutang' => [
            'Sebab gagal bayar hutang',
            'Sebab Gagal Bayar',
        ],
        'nama_pemberi_hutang' => [
            'Nama Pemberi Hutang',
            'Pemberi Hutang',
        ],
        'no_kp_pemberi_hutang' => [
            'No. K/P Pemberi Hutang',
            'No K/P Pemberi Hutang',
            'No KP Pemberi Hutang',
        ],

        // --- Borang Bantuan MAIJ: Section 7 (Tempat Tinggal) ---
        'tempat_tinggal_sendiri' => [
            'Sendiri',
            'Tempat Tinggal Sendiri',
        ],
        'tempat_tinggal_menumpang' => [
            'Menumpang',
        ],
        'tempat_tinggal_menyewe' => [
            'Menyewe',
            'Menyewa',
        ],
        'nilai_tempat_tinggal' => [
            'RM',
            'Nilai',
        ],
        'lain_lain_nyatakan_tempat_tinggal' => [
            'Lain-lain Nyatakan',
            'Lain-lain',
        ],

        // --- Borang Bantuan MAIJ: Section 8 (Harta) ---
        'harta_rumah' => [
            'Rumah',
        ],
        'harta_unit_rumah' => [
            'Unit',
            'Bilangan Unit Rumah',
        ],
        'harta_nilai_rumah' => [
            'Nilai RM',
            'Nilai Rumah',
        ],
        'harta_tanah' => [
            'Tanah',
        ],
        'harta_ekar' => [
            'Ekar',
        ],
        'harta_nilai_tanah' => [
            'Nilai Tanah',
        ],
        'harta_kereta' => [
            'Kereta',
        ],
        'harta_unit_kereta' => [
            'Unit Kereta',
        ],
        'harta_nilai_kereta' => [
            'Nilai Kereta',
        ],
        'harta_motosikal' => [
            'Motosikal',
        ],
        'harta_unit_motosikal' => [
            'Unit Motosikal',
        ],
        'harta_nilai_motosikal' => [
            'Nilai Motosikal',
        ],

        // --- Borang Bantuan MAIJ: Section 9 (Maklumat Pekerjaan) ---
        'pekerjaan_pemohon_nama' => [
            'Nama',
            'Nama Pemohon',
        ],
        'pekerjaan_pemohon_no_kp' => [
            'No. K/P',
            'No K/P',
        ],
        'pekerjaan_pemohon_majikan' => [
            'Majikan',
        ],
        'pekerjaan_pemohon_jawatan' => [
            'Pekerjaan',
            'Jawatan',
        ],
        'pekerjaan_pemohon_pendapatan' => [
            'Pendapatan',
        ],
        'pekerjaan_pasangan_nama' => [
            'Nama Pasangan',
        ],
        'pekerjaan_pasangan_no_kp' => [
            'No. K/P Pasangan',
        ],
        'pekerjaan_pasangan_majikan' => [
            'Majikan Pasangan',
        ],
        'pekerjaan_pasangan_jawatan' => [
            'Pekerjaan Pasangan',
        ],
        'pekerjaan_pasangan_pendapatan' => [
            'Pendapatan Pasangan',
        ],

        // --- Borang Bantuan MAIJ: Section 10 (Pendapatan Bulanan) ---
        'pendapatan_diri' => [
            'Diri',
            'Pendapatan Diri',
        ],
        'pendapatan_pasangan_ibubapa_penjaga' => [
            'Pasangan/Ibubapa/Penjaga',
            'Pasangan/IbuBapa/Penjaga',
            'Pasangan/Ibu Bapa/Penjaga',
        ],
        'pendapatan_pencen_perkeso' => [
            'Pencen/PERKESO',
            'Pencen PERKESO',
        ],
        'pendapatan_sumbangan_anak' => [
            'Sumbangan Anak-anak',
            'Sumbangan Anak',
        ],
        'pendapatan_bantuan_agensi' => [
            'Bantuan dari agensi lain',
            'Bantuan Agensi',
        ],
        'pendapatan_lain_lain' => [
            'Lain-lain pendapatan',
            'Lain-lain',
        ],
        'jumlah_pendapatan' => [
            'JUMLAH PENDAPATAN (RM)',
            'Jumlah Pendapatan',
        ],

        // --- Borang Bantuan MAIJ: Section 10 (Perbelanjaan Bulanan) ---
        'perbelanjaan_makan_minum' => [
            'Makan/Minum',
            'Makan Minum',
        ],
        'perbelanjaan_bil_api_air' => [
            'Bil Api/Air',
            'Bil Api Air',
        ],
        'perbelanjaan_persekolahan' => [
            'Persekolahan',
        ],
        'perbelanjaan_sewa_ansuran' => [
            'Sewa/Ansuran Rumah',
            'Sewa Ansuran',
        ],
    ];

    public static function fieldCategories(): array
    {
        return [
            'applicant' => ['nama', 'ic', 'ic_ketua_rumah', 'hubungan', 'status_perkahwinan', 'merokok'],
            'contact' => ['telefon', 'no_telefon_bimbit', 'no_telefon_rumah'],
            'address' => ['alamat', 'daerah', 'bandar', 'poskod', 'dun', 'parlimen'],
            'health' => ['kesihatan'],
            'financial' => ['pendapatan', 'pendapatan_pasangan', 'pendapatan_isi_rumah', 'pendapatan_lain', 'perbelanjaan', 'sewa', 'utiliti', 'pengangkutan', 'perubatan', 'perumahan', 'bilangan_tanggungan'],
            'application' => ['bantuan_sara_hidup', 'bantuan_perubatan', 'bantuan_berdikari', 'bantuan_perumahan', 'menjelaskan_hutang', 'jumlah_bantuan_diperlukan', 'sebab_memohon_bantuan'],
            'household_members' => ['isi_rumah_nama', 'isi_rumah_ic', 'isi_rumah_hubungan', 'isi_rumah_umur', 'isi_rumah_pekerjaan', 'isi_rumah_pendapatan', 'isi_rumah_pendidikan', 'isi_rumah_kesihatan'],
            'borang_bantuan' => [
                'jumlah_sara_hidup', 'no_akaun_bimb', 'bantuan_agensi_lain_ada', 'bantuan_agensi_lain_nyatakan', 'bantuan_agensi_lain_jumlah', 'tidak_pernah_terima_bantuan',
                'perubatan_jenis_rawatan', 'perubatan_pemohon_or_tanggungan', 'perubatan_nama_tanggungan', 'perubatan_hubungan_tanggungan', 'perubatan_no_kp_tanggungan', 'perubatan_jenis_penyakit', 'perubatan_mula_mengidap', 'perubatan_tempat_rawatan', 'perubatan_kos_rawatan', 'perubatan_harga_alatan',
                'jenis_berdikari', 'berdikari_jenis_peralatan', 'berdikari_harga_peralatan', 'berdikari_jenis_perniagaan', 'berdikari_sebab_memerlukan', 'berdikari_pengalaman_ada', 'berdikari_pengalaman_tahun',
                'jenis_bantuan_perumahan', 'status_tanah', 'nama_dalam_geran', 'no_geran', 'perumahan_laporan_polis',
                'sebab_berhutang', 'jumlah_hutang', 'sebab_gagal_bayar_hutang', 'nama_pemberi_hutang', 'no_kp_pemberi_hutang',
                'tempat_tinggal_sendiri', 'tempat_tinggal_menumpang', 'tempat_tinggal_menyewe', 'nilai_tempat_tinggal', 'lain_lain_nyatakan_tempat_tinggal',
                'harta_rumah', 'harta_unit_rumah', 'harta_nilai_rumah', 'harta_tanah', 'harta_ekar', 'harta_nilai_tanah', 'harta_kereta', 'harta_unit_kereta', 'harta_nilai_kereta', 'harta_motosikal', 'harta_unit_motosikal', 'harta_nilai_motosikal',
                'pekerjaan_pemohon_nama', 'pekerjaan_pemohon_no_kp', 'pekerjaan_pemohon_majikan', 'pekerjaan_pemohon_jawatan', 'pekerjaan_pemohon_pendapatan',
                'pekerjaan_pasangan_nama', 'pekerjaan_pasangan_no_kp', 'pekerjaan_pasangan_majikan', 'pekerjaan_pasangan_jawatan', 'pekerjaan_pasangan_pendapatan',
                'pendapatan_diri', 'pendapatan_pasangan_ibubapa_penjaga', 'pendapatan_pencen_perkeso', 'pendapatan_sumbangan_anak', 'pendapatan_bantuan_agensi', 'pendapatan_lain_lain', 'jumlah_pendapatan',
                'perbelanjaan_makan_minum', 'perbelanjaan_bil_api_air', 'perbelanjaan_persekolahan', 'perbelanjaan_sewa_ansuran',
            ],
        ];
    }

    public static function sectionLabels(): array
    {
        return [
            'MAKLUMAT PEMOHON' => 'applicant',
            'MAKLUMAT PEMOHAN' => 'applicant',
            'APPLICANT INFORMATION' => 'applicant',
            'MAKLUMAT PERIBADI' => 'applicant',
            'MAKLUMAT PENGENALAN PEMOHON' => 'applicant',
            'MAKLUMAT ALAMAT' => 'address',
            'ALAMAT SURAT MENYURAT' => 'address',
            'MAILING ADDRESS' => 'address',
            'MAKLUMAT KESIHATAN' => 'health',
            'HEALTH INFORMATION' => 'health',
            'MAKLUMAT KEWANGAN' => 'financial',
            'MAKLUMAT PENDAPATAN' => 'financial',
            'FINANCIAL INFORMATION' => 'financial',
            'INCOME INFORMATION' => 'financial',
            'MAKLUMAT PERMOHONAN BANTUAN' => 'application',
            'JENIS BANTUAN' => 'application',
            'BANTUAN DIPOHON' => 'application',
            'PERMOHONAN BANTUAN' => 'application',
            'ASSISTANCE REQUESTED' => 'application',
            'TYPE OF ASSISTANCE' => 'application',
            'MAKLUMAT ISI RUMAH' => 'household_members',
            'MAKLUMAT AHLI KELUARGA' => 'household_members',
            'SENARAI AHLI ISI RUMAH' => 'household_members',
            'HOUSEHOLD INFORMATION' => 'household_members',
            'FAMILY MEMBERS' => 'household_members',
            'HOUSEHOLD MEMBERS' => 'household_members',
            'MAKLUMAT TANGGUNGAN' => 'household_members',
            'MAKLUMAT TANGGUNGAN ATAU WARIS' => 'household_members',
            'MAKLUMAT TAMBAHAN PERMOHONAN BANTUAN' => 'borang_bantuan',
            'MAKLUMAT TAMBAHAN' => 'borang_bantuan',
            'MAKLUMAT TEMPAT TINGGAL' => 'borang_bantuan',
            'MAKLUMAT HARTA' => 'borang_bantuan',
            'MAKLUMAT PEKERJAAN' => 'borang_bantuan',
            'MAKLUMAT PEKERJAAN PEMOHON/PASANGAN' => 'borang_bantuan',
            'MAKLUMAT PENDAPATAN & PERBELANJAAN' => 'borang_bantuan',
            'MAKLUMAT PENDAPATAN DAN PERBELANJAAN' => 'borang_bantuan',
            'MAKLUMAT PERBELANJAAN BULANAN' => 'borang_bantuan',
        ];
    }

    public static function tabularFields(): array
    {
        return ['isi_rumah_nama', 'isi_rumah_ic', 'isi_rumah_hubungan', 'isi_rumah_umur', 'isi_rumah_pekerjaan', 'isi_rumah_pendapatan', 'isi_rumah_pendidikan', 'isi_rumah_kesihatan'];
    }

    public static function formatHints(): array
    {
        return [
            'ic' => '12-digit Malaysian IC number (e.g., 800315015678)',
            'ic_ketua_rumah' => '12-digit Malaysian IC number',
            'telefon' => 'Phone number starting with 0 (e.g., 0123456789)',
            'no_telefon_bimbit' => 'Mobile number starting with 01x',
            'no_telefon_rumah' => 'Landline number starting with 0x',
            'poskod' => '5-digit postal code',
            'pendapatan' => 'Numeric value in RM (e.g., 1500)',
            'sewa' => 'Numeric value in RM',
            'utiliti' => 'Numeric value in RM',
            'status_perkahwinan' => 'One of: Bujang, Berkahwin, Duda, Janda, Single, Married, Divorced, Widowed',
            'perumahan' => 'One of: Sendiri, Sewa, Own, Rent',
            'merokok' => 'Ya or Tidak',
        ];
    }

    public static function fieldForLabel(string $label): ?string
    {
        $normalized = mb_strtolower(trim($label));

        foreach (static::$labels as $field => $variants) {
            foreach ($variants as $variant) {
                if (mb_strtolower($variant) === $normalized) {
                    return $field;
                }
            }
        }

        return null;
    }

    public static function labelsFor(string $field): array
    {
        return static::$labels[$field] ?? [];
    }

    public static function fields(): array
    {
        return array_keys(static::$labels);
    }

    public static function primaryLabel(string $field): string
    {
        return static::$labels[$field][0] ?? $field;
    }

    public static function toPromptText(): string
    {
        $lines = [];

        foreach (static::$labels as $field => $variants) {
            $hint = static::formatHints()[$field] ?? '';
            $line = "- {$field}: ".implode(', ', array_slice($variants, 0, 4));
            if ($hint) {
                $line .= " [{$hint}]";
            }
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
