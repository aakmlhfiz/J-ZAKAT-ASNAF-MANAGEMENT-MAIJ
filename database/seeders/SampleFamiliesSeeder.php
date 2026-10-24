<?php

namespace Database\Seeders;

use App\Models\ProfilAsnaf;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleFamiliesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding 4 sample families...');

        $this->seedFamilySulong();
        $this->seedFamilyLim();
        $this->seedFamilyMutu();
        $this->seedFamilyRazak();

        $this->command?->info('Sample families seeded successfully.');
        $this->command?->warn('Run "php artisan household:backfill" to generate Household / HouseholdMember / AssistanceHistory records.');
    }

    private function seedFamilySulong(): void
    {
        $ketuaIC = '750612015001';
        $alamat = 'No. 23, Jalan Bukit Kempas, Taman Maju';

        $ketua = ProfilAsnaf::updateOrCreate(
            ['ic' => $ketuaIC],
            [
                'nama' => 'Sulong bin Ahmad',
                'ic_ketua_rumah' => null,
                'hubungan' => 'Ketua Isi Rumah',
                'status_perkahwinan' => 'Berkahwin',
                'merokok' => false,
                'telefon' => '0134567001',
                'no_telefon_bimbit' => '0134567001',
                'no_telefon_rumah' => '077123456',
                'kesihatan' => 'Sihat',
                'alamat' => $alamat,
                'daerah' => 'Johor Bahru',
                'bandar' => 'Johor Bahru',
                'poskod' => '81200',
                'dun' => 'Kempas',
                'parlimen' => 'Johor Bahru',
                'pendapatan' => 1800.00,
                'bilangan_tanggungan' => 4,
                'sewa' => 0,
                'utiliti' => 250.00,
                'n_isteri_suami' => 1,
                'n_ibu_bapa' => 0,
                'n_dewasa_tidak_bekerja' => 0,
                'n_dewasa_bekerja_tanggung' => 0,
                'n_ipt' => 0,
                'n_remaja' => 1,
                'n_kanak_rendah' => 1,
                'n_kanak_tadika' => 1,
                'perumahan' => 'sendiri',
                'pengangkutan_input' => 200.00,
                'perubatan_input' => 100.00,
                'sebab_memohon' => 'Pendapatan tidak mencukupi untuk menampung perbelanjaan keluarga yang ramai.',
                'status_kelayakan' => 'Layak',
                'approval_status' => 'approved',
                'approved_at' => Carbon::create(2026, 5, 10),
            ]
        );
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 92;
        $ketua->save();

        $this->seedMember($ketuaIC, 'Sarimah binti Ali', '771023015234', 'Isteri', $alamat, 1200.00, 'Layak', Carbon::create(2026, 5, 10), 0);
        $this->seedMember($ketuaIC, 'Aisyah binti Sulong', '050615015001', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 5, 12), 0);
        $this->seedMember($ketuaIC, 'Amirul bin Sulong', '070308015002', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 5, 12), 0);
        $this->seedMember($ketuaIC, 'Izzat bin Sulong', '100122015003', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 5, 12), 0);
    }

    private function seedFamilyLim(): void
    {
        $ketuaIC = '820803085002';
        $alamat = 'No. 47, Lorong Meranti, Taman Sentosa';

        $ketua = ProfilAsnaf::updateOrCreate(
            ['ic' => $ketuaIC],
            [
                'nama' => 'Lim Ah Choo',
                'ic_ketua_rumah' => null,
                'hubungan' => 'Ketua Isi Rumah',
                'status_perkahwinan' => 'Berkahwin',
                'merokok' => false,
                'telefon' => '0198877002',
                'no_telefon_bimbit' => '0198877002',
                'no_telefon_rumah' => '033345678',
                'kesihatan' => 'Kencing Manis',
                'alamat' => $alamat,
                'daerah' => 'Klang',
                'bandar' => 'Klang',
                'poskod' => '41000',
                'dun' => 'Meru',
                'parlimen' => 'Klang',
                'pendapatan' => 0,
                'bilangan_tanggungan' => 3,
                'sewa' => 650.00,
                'utiliti' => 180.00,
                'n_isteri_suami' => 1,
                'n_ibu_bapa' => 0,
                'n_dewasa_tidak_bekerja' => 1,
                'n_dewasa_bekerja_tanggung' => 0,
                'n_ipt' => 0,
                'n_remaja' => 0,
                'n_kanak_rendah' => 1,
                'n_kanak_tadika' => 1,
                'perumahan' => 'sewa',
                'pengangkutan_input' => 80.00,
                'perubatan_input' => 350.00,
                'sebab_memohon' => 'Suami hilang pekerjaan, perlu bantuan sara hidup dan perubatan untuk kencing manis.',
                'status_kelayakan' => 'Layak',
                'approval_status' => 'approved',
                'approved_at' => Carbon::create(2026, 6, 1),
            ]
        );
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 88;
        $ketua->save();

        $this->seedMember($ketuaIC, 'Tan Beng Huat', '800405085111', 'Suami', $alamat, 0, 'Layak', Carbon::create(2026, 6, 1), 0);
        $this->seedMember($ketuaIC, 'Nurul Ain binti Tan', '090622085003', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 6, 3), 0);
        $this->seedMember($ketuaIC, 'Aisyah binti Tan', '110815085004', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 6, 3), 0);
    }

    private function seedFamilyMutu(): void
    {
        $ketuaIC = '681125105003';
        $alamat = 'No. 12, Kampung Orang Asli, Batu 8';

        $ketua = ProfilAsnaf::updateOrCreate(
            ['ic' => $ketuaIC],
            [
                'nama' => 'Mutu anak Bujang',
                'ic_ketua_rumah' => null,
                'hubungan' => 'Ketua Isi Rumah',
                'status_perkahwinan' => 'Berkahwin',
                'merokok' => true,
                'telefon' => '0145566003',
                'no_telefon_bimbit' => '0145566003',
                'no_telefon_rumah' => null,
                'kesihatan' => 'Sakit Sendi',
                'alamat' => $alamat,
                'daerah' => 'Kuantan',
                'bandar' => 'Kuantan',
                'poskod' => '25100',
                'dun' => 'Beserah',
                'parlimen' => 'Kuantan',
                'pendapatan' => 950.00,
                'bilangan_tanggungan' => 5,
                'sewa' => 0,
                'utiliti' => 120.00,
                'n_isteri_suami' => 1,
                'n_ibu_bapa' => 0,
                'n_dewasa_tidak_bekerja' => 0,
                'n_dewasa_bekerja_tanggung' => 1,
                'n_ipt' => 0,
                'n_remaja' => 1,
                'n_kanak_rendah' => 2,
                'n_kanak_tadika' => 1,
                'perumahan' => 'sendiri',
                'pengangkutan_input' => 100.00,
                'perubatan_input' => 200.00,
                'sebab_memohon' => 'Pendapatan kerja kampung tidak stabil, mempunyai ramai tanggungan termasuk anak-anak yang masih bersekolah.',
                'status_kelayakan' => 'Layak',
                'approval_status' => 'approved',
                'approved_at' => Carbon::create(2026, 4, 20),
            ]
        );
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 94;
        $ketua->save();

        $this->seedMember($ketuaIC, 'Ngat binti Seban', '700812105234', 'Isteri', $alamat, 600.00, 'Layak', Carbon::create(2026, 4, 20), 0);
        $this->seedMember($ketuaIC, 'Sumbing anak Mutu', '920524105005', 'Anak', $alamat, 850.00, 'Layak', Carbon::create(2026, 4, 22), 0);
        $this->seedMember($ketuaIC, 'Lahang anak Mutu', '950312105006', 'Anak', $alamat, 900.00, 'Layak', Carbon::create(2026, 4, 22), 0);
        $this->seedMember($ketuaIC, 'Roslan anak Mutu', '030815105007', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 4, 22), 0);
        $this->seedMember($ketuaIC, 'Mazlan anak Mutu', '051020105008', 'Anak', $alamat, 0, 'Layak', Carbon::create(2026, 4, 22), 0);
    }

    private function seedFamilyRazak(): void
    {
        $ketuaIC = '790724015004';
        $alamat = 'No. 88, Jalan Sultanah Zainab, Kampung Laut';

        $ketua = ProfilAsnaf::updateOrCreate(
            ['ic' => $ketuaIC],
            [
                'nama' => 'Mohd Razak bin Hassan',
                'ic_ketua_rumah' => null,
                'hubungan' => 'Ketua Isi Rumah',
                'status_perkahwinan' => 'Berkahwin',
                'merokok' => false,
                'telefon' => '0167788004',
                'no_telefon_bimbit' => '0167788004',
                'no_telefon_rumah' => '097456789',
                'kesihatan' => 'Sihat',
                'alamat' => $alamat,
                'daerah' => 'Kota Bharu',
                'bandar' => 'Kota Bharu',
                'poskod' => '15000',
                'dun' => 'Kota Bharu',
                'parlimen' => 'Kota Bharu',
                'pendapatan' => 2200.00,
                'bilangan_tanggungan' => 3,
                'sewa' => 400.00,
                'utiliti' => 200.00,
                'n_isteri_suami' => 1,
                'n_ibu_bapa' => 0,
                'n_dewasa_tidak_bekerja' => 0,
                'n_dewasa_bekerja_tanggung' => 0,
                'n_ipt' => 1,
                'n_remaja' => 0,
                'n_kanak_rendah' => 1,
                'n_kanak_tadika' => 0,
                'perumahan' => 'sewa',
                'pengangkutan_input' => 250.00,
                'perubatan_input' => 100.00,
                'sebab_memohon' => 'Bantuan IPT untuk anak yang belajar di universiti dan tambahan kos persekolahan.',
                'status_kelayakan' => 'Dalam Semakan',
                'approval_status' => 'pending',
                'approved_at' => null,
            ]
        );
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 72;
        $ketua->save();

        $this->seedMember($ketuaIC, 'Rosmawati binti Ismail', '820912015888', 'Isteri', $alamat, 1500.00, 'Dalam Semakan', null, 0);
        $this->seedMember($ketuaIC, 'Hafiz bin Mohd Razak', '030625015005', 'Anak', $alamat, 0, 'Dalam Semakan', null, 0);
        $this->seedMember($ketuaIC, 'Nadia binti Mohd Razak', '060118015006', 'Anak', $alamat, 0, 'Dalam Semakan', null, 0);
    }

    private function seedMember(
        string $ketuaIC,
        string $nama,
        string $ic,
        string $hubungan,
        string $alamat,
        float $pendapatan,
        string $statusKelayakan,
        ?Carbon $approvedAt,
        int $bilanganTanggungan
    ): void {
        ProfilAsnaf::updateOrCreate(
            ['ic' => $ic],
            [
                'nama' => $nama,
                'ic_ketua_rumah' => $ketuaIC,
                'hubungan' => $hubungan,
                'status_perkahwinan' => 'Bujang',
                'merokok' => false,
                'telefon' => null,
                'alamat' => $alamat,
                'pendapatan' => $pendapatan,
                'bilangan_tanggungan' => $bilanganTanggungan,
                'sewa' => 0,
                'utiliti' => 0,
                'n_isteri_suami' => 0,
                'n_ibu_bapa' => 0,
                'n_dewasa_tidak_bekerja' => 0,
                'n_dewasa_bekerja_tanggung' => 0,
                'n_ipt' => 0,
                'n_remaja' => 0,
                'n_kanak_rendah' => 0,
                'n_kanak_tadika' => 0,
                'perumahan' => 'sendiri',
                'pengangkutan_input' => 0,
                'perubatan_input' => 0,
                'status_kelayakan' => $statusKelayakan,
                'approval_status' => $approvedAt ? 'approved' : 'pending',
                'approved_at' => $approvedAt,
            ]
        );
    }

    private function calcHadKifayah(ProfilAsnaf $p): float
    {
        $had = 1316.00;
        $had += ($p->n_isteri_suami ?? 0) * 162.00;
        $had += ($p->n_ibu_bapa ?? 0) * 156.00;
        $had += ($p->n_ipt ?? 0) * 600.00;
        $had += ($p->n_remaja ?? 0) * 304.00;
        $had += ($p->n_kanak_rendah ?? 0) * 254.00;
        $had += ($p->n_kanak_tadika ?? 0) * 185.00;

        if (($p->perumahan ?? 'sendiri') === 'sewa') {
            $had += (float) ($p->sewa ?? 0);
        }

        $had += (float) ($p->utiliti ?? 0);
        $had += (float) ($p->pengangkutan_input ?? 0);
        $had += (float) ($p->perubatan_input ?? 0);

        return round($had, 2);
    }
}
