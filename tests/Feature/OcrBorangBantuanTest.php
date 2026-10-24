<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\DocumentImport;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use App\Services\Ocr\BorangBantuanRecognizer;
use App\Services\Ocr\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OcrBorangBantuanTest extends TestCase
{
    use RefreshDatabase;

    private PortalUser $portalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupPortalUserTable();

        $this->portalUser = PortalUser::factory()->create([
            'email' => 'pemohon-'.uniqid().'@maij.gov.my',
            'ic_no' => '800315015678',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanupPortalUserTable();
        parent::tearDown();
    }

    private function cleanupPortalUserTable(): void
    {
        try {
            DB::connection('user')->table('users')->delete();
        } catch (\Throwable $e) {
            // Connection may not be available; ignore.
        }
    }

    public function test_borang_recognizer_extracts_application_income_and_household_fields(): void
    {
        $sampleText = <<<'TEXT'
MAJLIS AGAMA ISLAM NEGERI JOHOR
(BAHAGIAN AGIHAN ZAKAT)

4. MAKLUMAT PENGENALAN PEMOHON
No. K/P: 800315015678
Nama Pemohon: Ahmad Bin Ali
Alamat Surat Menyurat: 123 Jalan Mawar, Taman Bahagia
Daerah: Johor Bahru
Poskod: 80000
Bandar: Johor Bahru
DUN: Stulang
Parlimen: Johor Bahru
No. Telefon Bimbit: 0123456789
No. Telefon Rumah: 07-1234567
Kesihatan: Sihat
Status: Berkahwin
Merokok: Tidak

3. MAKLUMAT PERMOHONAN BANTUAN
[✓] Bantuan Sara Hidup [ ] Bantuan Perubatan [ ] Bantuan Berdikari [ ] Bantuan Perumahan [ ] Menjelaskan Hutang
Jumlah (RM) diperlukan: 500
Sebab memohon bantuan: Kehilangan pekerjaan

5. MAKLUMAT TAMBAHAN
A. SARA HIDUP
JUM. Yang Diperlukan : RM 500
No. Akaun BIMB: 1234567890
Bantuan daripada Agensi lain [✓] Ada [ ] Tidak
Tidak pernah menerima bantuan dari agensi lain [ ]

B. PERUBATAN
[ ] Dialisis [ ] Pembedahan
Pemohon [✓] Tanggungan [ ]
Nama Tanggung: Ahmad Bin Ali
Hubungan: Diri
No. Kad Pengenalan: 800315015678
Jenis Penyakit: Darah Tinggi
Tempat Rawatan: Hospital Sultanah Aminah
Kos Rawatan : RM 200

C. BERDIKARI
[ ] Peralatan [✓] Modal
Jenis peralatan yang dipohon: Mesin jahit
Harga : RM 1500
Jenis Perniagaan: Jahitan
Sebab memerlukan: Membantu pendapatan keluarga
Pengalaman Berniaga berapa tahun [ ] Ada [ ] Tiada

D. PERUMAHAN
[ ] Bina Baru [ ] Baik Pulih
Status Tanah [✓] Sendiri [ ] Sewa [ ] Tumpang
Nama dalam Geran: Ahmad Bin Ali
No. Geran: GR123456

E. MENJELASKAN HUTANG
Sebab berhutang: Kos perubatan
Jumlah Hutang : RM 3000
Sebab gagal bayar hutang: Tiada pendapatan
Nama Pemberi Hutang: Bank Islam
No. K/P Pemberi Hutang: 800101012345

7. MAKLUMAT TEMPAT TINGGAL
[ ] Sendiri [ ] Menumpang [ ] Menyewe
Lain-lain Nyatakan: Rumah Anak

8. MAKLUMAT HARTA
[✓] Rumah Unit 1 Nilai RM 300000
[ ] Tanah
[ ] Kereta
[ ] Motosikal

10. MAKLUMAT PENDAPATAN & PERBELANJAAN
Pendapatan:
Diri RM 1500
Pasangan/Ibubapa/Penjaga RM 0
Pencen/PERKESO RM 0
Sumbangan Anak-anak RM 200
Bantuan dari agensi lain RM 0
Lain-lain pendapatan RM 0
JUMLAH PENDAPATAN (RM) 1700

Perbelanjaan:
Makan/Minum RM 600
Bil Api/Air RM 100
Persekolahan RM 0
Pengangkutan RM 100
Perubatan RM 200
Sewa/Ansuran Rumah RM 0
Lain-lain RM 50
TEXT;

        $recognizer = new BorangBantuanRecognizer;
        $result = $recognizer->recognize($sampleText);

        $this->assertArrayHasKey('fields', $result);
        $fields = $result['fields'];

        $this->assertSame('Ahmad Bin Ali', $fields['nama']['value'] ?? null);
        $this->assertSame('800315015678', $fields['ic']['value'] ?? null);
        $this->assertSame('012-3456 789', $fields['no_telefon_bimbit']['value'] ?? null);
        $this->assertSame('Johor Bahru', $fields['daerah']['value'] ?? null);
        $this->assertSame('80000', $fields['poskod']['value'] ?? null);

        $this->assertSame('Ya', $fields['bantuan_sara_hidup']['value'] ?? null);

        $this->assertSame('Darah Tinggi', $fields['perubatan_jenis_penyakit']['value'] ?? null);
        $this->assertSame('Hospital Sultanah Aminah', $fields['perubatan_tempat_rawatan']['value'] ?? null);

        $this->assertSame('Modal', $fields['jenis_berdikari']['value'] ?? null);
        $this->assertSame('Mesin jahit', $fields['berdikari_jenis_peralatan']['value'] ?? null);

        $this->assertSame('Sendiri', $fields['status_tanah']['value'] ?? null);
        $this->assertSame('Ahmad Bin Ali', $fields['nama_dalam_geran']['value'] ?? null);
        $this->assertSame('GR123456', $fields['no_geran']['value'] ?? null);

        $this->assertSame('RM 3000', $fields['jumlah_hutang']['value'] ?? null);
        $this->assertSame('Bank Islam', $fields['nama_pemberi_hutang']['value'] ?? null);

        $this->assertSame('Ya', $fields['harta_rumah']['value'] ?? null);

        $this->assertArrayHasKey('borang', $result);
        $this->assertArrayHasKey('pendapatan', $result['borang']);
        $this->assertArrayHasKey('perbelanjaan', $result['borang']);
    }

    public function test_portal_scan_upload_creates_document_import_with_borang_type(): void
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('borang_bantuan.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->portalUser, 'portal')
            ->post(route('user.applications.scan.upload'), ['file' => $file]);

        $response->assertRedirect();

        $this->assertDatabaseHas('document_imports', [
            'original_filename' => 'borang_bantuan.pdf',
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'template' => 'Borang Agihan Zakat',
            'status' => 'processing',
            'portal_user_id' => $this->portalUser->id,
        ]);
    }

    public function test_portal_scan_upload_rejects_invalid_file_format(): void
    {
        $file = UploadedFile::fake()->create('document.docx', 100);

        $response = $this->actingAs($this->portalUser, 'portal')
            ->post(route('user.applications.scan.upload'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_portal_scan_index_is_accessible_to_authenticated_portal_user(): void
    {
        $response = $this->actingAs($this->portalUser, 'portal')
            ->get(route('user.applications.scan.index'));

        $response->assertStatus(200);
        $response->assertSee('Borang Bantuan MAIJ');
    }

    public function test_portal_scan_commit_creates_application_and_profil_with_ai_score(): void
    {
        $import = DocumentImport::create([
            'portal_user_id' => $this->portalUser->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'test_hash_borang',
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'template' => 'Borang Agihan Zakat',
            'status' => 'extracted',
            'page_count' => 1,
            'extracted_data' => [
                'nama' => ['value' => 'Ahmad Bin Ali', 'confidence' => 95],
                'ic' => ['value' => '800315015678', 'confidence' => 95],
                'pendapatan' => ['value' => '1500', 'confidence' => 80],
                'alamat' => ['value' => '123 Jalan Mawar', 'confidence' => 80],
                'daerah' => ['value' => 'Johor Bahru', 'confidence' => 80],
                'poskod' => ['value' => '80000', 'confidence' => 80],
                'no_telefon_bimbit' => ['value' => '0123456789', 'confidence' => 80],
                'status_perkahwinan' => ['value' => 'Berkahwin', 'confidence' => 80],
                'bilangan_tanggungan' => ['value' => '4', 'confidence' => 80],
                'bantuan_sara_hidup' => ['value' => 'Ya', 'confidence' => 80],
                'sebab_memohon_bantuan' => ['value' => 'Kehilangan pekerjaan', 'confidence' => 80],
            ],
        ]);

        $response = $this->actingAs($this->portalUser, 'portal')
            ->post(route('user.applications.scan.commit', $import->id), [
                'full_name' => 'Ahmad Bin Ali',
                'ic_no' => '800315015678',
                'phone' => '0123456789',
                'email' => 'pemohon@maij.gov.my',
                'address' => '123 Jalan Mawar',
                'district' => 'Johor Bahru',
                'state' => 'Johor',
                'postcode' => '80000',
                'marital_status' => 'married',
                'household_count' => 4,
                'monthly_income' => 1500,
                'spouse_income' => 0,
                'occupation' => 'Pekerja Am',
                'confirm' => 1,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'user_id' => $this->portalUser->id,
            'ic_no' => '800315015678',
            'full_name' => 'Ahmad Bin Ali',
            'status' => 'submitted',
            'borang_scan_id' => $import->id,
        ]);

        $this->assertDatabaseHas('profil_asnafs', [
            'ic' => '800315015678',
            'nama' => 'Ahmad Bin Ali',
            'pendapatan' => '1500.00',
        ]);

        $profil = ProfilAsnaf::where('ic', '800315015678')->first();
        $this->assertNotNull($profil, 'ProfilAsnaf should exist');
        $this->assertNotNull($profil->ai_score, 'AI score should be populated');
        $this->assertGreaterThan(0, (float) $profil->ai_score);
        $this->assertNotNull($profil->status_kelayakan);
        $this->assertNotNull($profil->borang_data, 'borang_data JSON should be persisted');
        $this->assertSame(OcrService::DOC_TYPE_BORANG_BANTUAN, $profil->borang_data['document_type'] ?? null);

        $application = Application::where('ic_no', '800315015678')->first();
        $this->assertNotNull($application->borang_data);
        $this->assertSame(OcrService::DOC_TYPE_BORANG_BANTUAN, $application->borang_data['document_type'] ?? null);

        $this->assertDatabaseHas('document_imports', [
            'id' => $import->id,
            'status' => 'committed',
            'profil_asnaf_ic' => '800315015678',
        ]);
    }

    public function test_portal_scan_commit_blocks_already_approved_profile(): void
    {
        ProfilAsnaf::create([
            'nama' => 'Ahmad Bin Ali',
            'ic' => '800315015678',
            'pendapatan' => 1500,
            'status_kelayakan' => 'Layak',
            'approval_status' => 'approved',
        ]);

        $import = DocumentImport::create([
            'portal_user_id' => $this->portalUser->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'test_hash_duplicate',
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'status' => 'extracted',
            'page_count' => 1,
        ]);

        $response = $this->actingAs($this->portalUser, 'portal')
            ->post(route('user.applications.scan.commit', $import->id), [
                'full_name' => 'Ahmad Bin Ali',
                'ic_no' => '800315015678',
                'phone' => '0123456789',
                'email' => 'pemohon@maij.gov.my',
                'household_count' => 4,
                'monthly_income' => 1500,
                'confirm' => 1,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_admin_upload_accepts_borang_document_type(): void
    {
        Queue::fake();
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $file = UploadedFile::fake()->create('borang.pdf', 200, 'application/pdf');

        $response = $this->actingAs($admin, 'web')
            ->post(route('import-pdf.upload'), [
                'file' => $file,
                'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('document_imports', [
            'document_type' => 'borang_bantuan_maij',
            'template' => 'Borang Agihan Zakat',
        ]);
    }

    public function test_admin_upload_rejects_invalid_document_type(): void
    {
        $admin = Admin::factory()->create();
        $file = UploadedFile::fake()->create('borang.pdf', 200, 'application/pdf');

        $response = $this->actingAs($admin, 'web')
            ->post(route('import-pdf.upload'), [
                'file' => $file,
                'document_type' => 'invalid_type',
            ]);

        $response->assertSessionHasErrors('document_type');
    }
}
