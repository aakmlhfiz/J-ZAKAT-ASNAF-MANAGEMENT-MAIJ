<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ProfilAsnafDataSeeder extends Seeder
{
    public function run(): void
    {
        if (ProfilAsnaf::count() > 0) {
            $this->command?->info('Profil Asnaf already exist — skipping.');

            return;
        }

        $this->seedPortalUsers();
        $this->seedFamilyAhmad();
        $this->seedFamilySiti();
        $this->seedFamilyRahim();
        $this->seedFamilyMeiLing();
        $this->seedApplications();

        $this->command?->info('18 profil asnafs + 4 applications created.');
    }

    private function seedPortalUsers(): void
    {
        if (PortalUser::where('email', 'rahim.test@gmail.com')->doesntExist()) {
            PortalUser::create([
                'name' => 'Abdul Rahim bin Hassan',
                'email' => 'rahim.test@gmail.com',
                'password' => Hash::make('password123'),
                'ic_no' => '780512015890',
                'phone' => '0123456890',
                'address' => 'No. 8, Jalan Mawar 3, Taman Seri Pulai, 81110 Kangkar Pulai, Johor Bahru, Johor',
                'district' => 'Johor Bahru',
                'state' => 'Johor',
                'date_of_birth' => '1978-05-12',
                'gender' => 'male',
                'status' => 'active',
            ]);
        }

        if (PortalUser::where('email', 'nordin.test@gmail.com')->doesntExist()) {
            PortalUser::create([
                'name' => 'Nordin bin Abdullah (Mualaf)',
                'email' => 'nordin.test@gmail.com',
                'password' => Hash::make('password123'),
                'ic_no' => '820720015123',
                'phone' => '0198765432',
                'address' => 'No. 25, Jalan Permas 10/5, Bandar Baru Permas Jaya, 81750 Masai, Johor',
                'district' => 'Johor Bahru',
                'state' => 'Johor',
                'date_of_birth' => '1982-07-20',
                'gender' => 'male',
                'status' => 'active',
            ]);
        }
    }

    private function seedFamilyAhmad(): void
    {
        $ketuaIC = '800315015678';
        $alamat = 'No. 12, Jalan Kempas Indah 5, Taman Kempas, 81200 Johor Bahru, Johor';

        $ketua = ProfilAsnaf::create([
            'nama' => 'Ahmad bin Abdullah',
            'ic' => $ketuaIC,
            'ic_ketua_rumah' => null,
            'hubungan' => 'Ketua Isi Rumah',
            'telefon' => '0134567890',
            'alamat' => $alamat,
            'pendapatan' => 450.00,
            'bilangan_tanggungan' => 4,
            'sewa' => 350.00,
            'utiliti' => 200.00,
            'n_isteri_suami' => 1,
            'n_ibu_bapa' => 0,
            'n_dewasa_tidak_bekerja' => 0,
            'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 1,
            'n_remaja' => 1,
            'n_kanak_rendah' => 1,
            'n_kanak_tadika' => 0,
            'perumahan' => 'sewa',
            'pengangkutan_input' => 150.00,
            'perubatan_input' => 500.00,
            'status_kelayakan' => 'Layak',
            'approval_status' => 'approved',
            'approved_at' => Carbon::create(2022, 3, 15),
        ]);
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 96;
        $ketua->save();

        ProfilAsnaf::create([
            'nama' => 'Fatimah binti Ismail',
            'ic' => '821107015432',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Isteri',
            'telefon' => '0134567891',
            'alamat' => $alamat,
            'pendapatan' => 350.00,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sewa',
            'status_kelayakan' => 'Layak',
            'approval_status' => 'approved',
            'approved_at' => Carbon::create(2024, 4, 5),
        ]);

        ProfilAsnaf::create([
            'nama' => 'Muhammad Ali bin Ahmad',
            'ic' => '040522014567',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => '0134567890',
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sewa',
            'status_kelayakan' => 'Layak',
            'approval_status' => 'approved',
            'approved_at' => Carbon::create(2025, 8, 15),
        ]);

        ProfilAsnaf::create([
            'nama' => 'Siti Aisyah binti Ahmad',
            'ic' => '080808014789',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sewa',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Adam bin Ahmad',
            'ic' => '120714014901',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sewa',
            'status_kelayakan' => 'Dalam Semakan',
        ]);
    }

    private function seedFamilySiti(): void
    {
        $ketuaIC = '850420015678';
        $alamat = 'Lot 456, Kampung Parit Haji Ali, 82100 Ayer Baloi, Pontian, Johor';

        $ketua = ProfilAsnaf::create([
            'nama' => 'Siti Hajar binti Mahmud',
            'ic' => $ketuaIC,
            'ic_ketua_rumah' => null,
            'hubungan' => 'Ketua Isi Rumah',
            'telefon' => '0176543210',
            'alamat' => $alamat,
            'pendapatan' => 900.00,
            'bilangan_tanggungan' => 3,
            'sewa' => 0,
            'utiliti' => 150.00,
            'n_isteri_suami' => 0,
            'n_ibu_bapa' => 0,
            'n_dewasa_tidak_bekerja' => 0,
            'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 0,
            'n_remaja' => 1,
            'n_kanak_rendah' => 2,
            'n_kanak_tadika' => 0,
            'perumahan' => 'sendiri',
            'pengangkutan_input' => 100.00,
            'perubatan_input' => 50.00,
            'status_kelayakan' => 'Layak',
            'approval_status' => 'approved',
            'approved_at' => Carbon::create(2022, 6, 1),
        ]);
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 88;
        $ketua->save();

        ProfilAsnaf::create([
            'nama' => 'Nurul Izzah binti Ismail',
            'ic' => '100618014567',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 100.00,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Muhammad Imran bin Ismail',
            'ic' => '130720014890',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Aisyah Humaira binti Ismail',
            'ic' => '180501014901',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);
    }

    private function seedFamilyRahim(): void
    {
        $ketuaIC = '780512015890';
        $alamat = 'No. 8, Jalan Mawar 3, Taman Seri Pulai, 81110 Kangkar Pulai, Johor Bahru, Johor';

        $ketua = ProfilAsnaf::create([
            'nama' => 'Abdul Rahim bin Hassan',
            'ic' => $ketuaIC,
            'ic_ketua_rumah' => null,
            'hubungan' => 'Ketua Isi Rumah',
            'telefon' => '0123456890',
            'alamat' => $alamat,
            'pendapatan' => 1500.00,
            'bilangan_tanggungan' => 4,
            'sewa' => 0,
            'utiliti' => 350.00,
            'n_isteri_suami' => 1,
            'n_ibu_bapa' => 1,
            'n_dewasa_tidak_bekerja' => 0,
            'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 1,
            'n_remaja' => 1,
            'n_kanak_rendah' => 0,
            'n_kanak_tadika' => 0,
            'perumahan' => 'sendiri',
            'pengangkutan_input' => 350.00,
            'perubatan_input' => 100.00,
            'status_kelayakan' => 'Dalam Semakan',
        ]);
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 55;
        $ketua->save();

        ProfilAsnaf::create([
            'nama' => 'Rohana binti Osman',
            'ic' => '800912015234',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Isteri',
            'telefon' => '0123456891',
            'alamat' => $alamat,
            'pendapatan' => 1000.00,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Tidak Layak',
            'approval_status' => 'rejected',
            'approved_at' => Carbon::create(2025, 11, 5),
            'approval_remarks' => 'Pendapatan melebihi Had Kifayah',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Hassan bin Abdul Rahim',
            'ic' => '050304014567',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Hasanah binti Abdul Rahim',
            'ic' => '090815014789',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Mak Cik Salmah binti Ahmad',
            'ic' => '480620015678',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Ibu',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);
    }

    private function seedFamilyMeiLing(): void
    {
        $ketuaIC = '820720015123';
        $alamat = 'No. 25, Jalan Permas 10/5, Bandar Baru Permas Jaya, 81750 Masai, Johor';

        $ketua = ProfilAsnaf::create([
            'nama' => 'Nordin bin Abdullah (Mualaf)',
            'ic' => $ketuaIC,
            'ic_ketua_rumah' => null,
            'hubungan' => 'Ketua Isi Rumah',
            'telefon' => '0198765432',
            'alamat' => $alamat,
            'pendapatan' => 1200.00,
            'bilangan_tanggungan' => 3,
            'sewa' => 0,
            'utiliti' => 250.00,
            'n_isteri_suami' => 1,
            'n_ibu_bapa' => 0,
            'n_dewasa_tidak_bekerja' => 0,
            'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 0,
            'n_remaja' => 1,
            'n_kanak_rendah' => 1,
            'n_kanak_tadika' => 0,
            'perumahan' => 'sendiri',
            'pengangkutan_input' => 200.00,
            'perubatan_input' => 50.00,
            'status_kelayakan' => 'Layak',
            'approval_status' => 'approved',
            'approved_at' => Carbon::create(2023, 8, 1),
        ]);
        $ketua->had_kifayah = $this->calcHadKifayah($ketua);
        $ketua->ai_score = 72;
        $ketua->save();

        ProfilAsnaf::create([
            'nama' => 'Nurul Huda binti Ahmad (Mualaf)',
            'ic' => '850330015234',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Isteri',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 600.00,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Aiman bin Nordin',
            'ic' => '110512014567',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Amirah binti Nordin',
            'ic' => '150706014890',
            'ic_ketua_rumah' => $ketuaIC,
            'hubungan' => 'Anak',
            'telefon' => null,
            'alamat' => $alamat,
            'pendapatan' => 0,
            'bilangan_tanggungan' => 0,
            'perumahan' => 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
        ]);
    }

    private function seedApplications(): void
    {
        $ahmadUser = PortalUser::where('ic_no', '850515108888')->first();
        $sitiUser = PortalUser::where('ic_no', '900101015678')->first();
        $rahimUser = PortalUser::where('ic_no', '780512015890')->first();
        $nordinUser = PortalUser::where('ic_no', '820720015123')->first();

        if ($ahmadUser) {
            Application::create([
                'user_id' => $ahmadUser->id,
                'application_no' => 'APP-2026-0001',
                'full_name' => 'Ahmad bin Abdullah',
                'ic_no' => '800315015678',
                'phone' => '0134567890',
                'email' => 'ahmad.test@gmail.com',
                'address' => 'No. 12, Jalan Kempas Indah 5, Taman Kempas, 81200 Johor Bahru, Johor',
                'district' => 'Johor Bahru',
                'state' => 'Johor',
                'marital_status' => 'married',
                'household_count' => 5,
                'occupation' => 'Pekerja Cucian',
                'monthly_income' => 450.00,
                'spouse_income' => 350.00,
                'total_household_income' => 800.00,
                'status' => 'approved',
                'submitted_at' => Carbon::create(2022, 3, 1),
                'reviewed_at' => Carbon::create(2022, 3, 10),
                'ai_score' => 96,
                'ai_recommendation' => 'approve',
            ]);
        }

        if ($sitiUser) {
            Application::create([
                'user_id' => $sitiUser->id,
                'application_no' => 'APP-2026-0002',
                'full_name' => 'Siti Hajar binti Mahmud',
                'ic_no' => '850420015678',
                'phone' => '0176543210',
                'email' => 'siti.aminah.test@gmail.com',
                'address' => 'Lot 456, Kampung Parit Haji Ali, 82100 Ayer Baloi, Pontian, Johor',
                'district' => 'Pontian',
                'state' => 'Johor',
                'marital_status' => 'widowed',
                'household_count' => 4,
                'occupation' => 'Pembantu Kedai',
                'monthly_income' => 900.00,
                'total_household_income' => 1200.00,
                'status' => 'approved',
                'submitted_at' => Carbon::create(2022, 5, 15),
                'reviewed_at' => Carbon::create(2022, 5, 25),
                'ai_score' => 88,
                'ai_recommendation' => 'approve',
            ]);
        }

        if ($rahimUser) {
            Application::create([
                'user_id' => $rahimUser->id,
                'application_no' => 'APP-2026-0003',
                'full_name' => 'Abdul Rahim bin Hassan',
                'ic_no' => '780512015890',
                'phone' => '0123456890',
                'email' => 'rahim.test@gmail.com',
                'address' => 'No. 8, Jalan Mawar 3, Taman Seri Pulai, 81110 Kangkar Pulai, Johor Bahru, Johor',
                'district' => 'Johor Bahru',
                'state' => 'Johor',
                'marital_status' => 'married',
                'household_count' => 5,
                'occupation' => 'Pengawal Keselamatan',
                'monthly_income' => 1500.00,
                'spouse_income' => 1000.00,
                'total_household_income' => 2500.00,
                'status' => 'rejected',
                'submitted_at' => Carbon::create(2025, 10, 1),
                'reviewed_at' => Carbon::create(2025, 10, 20),
                'rejected_at' => Carbon::create(2025, 11, 5),
                'admin_remarks' => 'Pendapatan melebihi Had Kifayah',
                'ai_score' => 55,
                'ai_recommendation' => 'reject',
            ]);
        }

        if ($nordinUser) {
            Application::create([
                'user_id' => $nordinUser->id,
                'application_no' => 'APP-2026-0004',
                'full_name' => 'Nordin bin Abdullah (Mualaf)',
                'ic_no' => '820720015123',
                'phone' => '0198765432',
                'email' => 'nordin.test@gmail.com',
                'address' => 'No. 25, Jalan Permas 10/5, Bandar Baru Permas Jaya, 81750 Masai, Johor',
                'district' => 'Johor Bahru',
                'state' => 'Johor',
                'marital_status' => 'married',
                'household_count' => 4,
                'occupation' => 'Pekerja Kontrak',
                'monthly_income' => 1200.00,
                'spouse_income' => 600.00,
                'total_household_income' => 1800.00,
                'status' => 'approved',
                'submitted_at' => Carbon::create(2023, 7, 10),
                'reviewed_at' => Carbon::create(2023, 7, 25),
                'ai_score' => 72,
                'ai_recommendation' => 'approve',
            ]);
        }
    }

    private function calcHadKifayah(ProfilAsnaf $p): float
    {
        $total = 1316.00;
        $total += (int) $p->n_isteri_suami * 162.00;
        $total += (int) $p->n_ibu_bapa * 156.00;
        $total += (int) $p->n_ipt * 600.00;
        $total += (int) $p->n_remaja * 304.00;
        $total += (int) $p->n_kanak_rendah * 254.00;
        $total += (int) $p->n_kanak_tadika * 185.00;
        if ($p->perumahan === 'sewa') {
            $total += (float) ($p->sewa ?? 0);
        }
        $total += (float) ($p->utiliti ?? 0);
        $total += (float) ($p->pengangkutan_input ?? 0);
        $total += (float) ($p->perubatan_input ?? 0);

        return round($total, 2);
    }
}
