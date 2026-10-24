<?php

namespace Tests\Feature;

use App\Mail\AdditionalDocumentsRequestedMail;
use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationReceivedMail;
use App\Mail\ResetPasswordMail;
use App\Models\Admin;
use App\Models\Application;
use App\Models\DocumentImport;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use App\Services\ApprovalService;
use Database\Seeders\HadKifayahSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemohonSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HadKifayahSettingSeeder::class);
    }

    // ---- S2: AI admin-only ----

    public function test_ai_page_requires_admin_auth(): void
    {
        $this->get('/ai')->assertRedirect(route('login'));

        $portalUser = PortalUser::factory()->create();
        $this->actingAs($portalUser, 'portal');
        $this->get('/ai')->assertRedirect(route('login'));
    }

    public function test_ai_chat_denied_for_portal_user(): void
    {
        $portalUser = PortalUser::factory()->create();
        $this->actingAs($portalUser, 'portal');

        $this->post('/ai/chat', ['message' => 'cari 900101011234'])->assertRedirect(route('login'));
    }

    // ---- S3: search auth ----

    public function test_public_search_endpoints_require_auth(): void
    {
        $this->get('/api/search?search=Ali')->assertRedirect(route('login'));
        $this->get('/api/household/search?q=Ali')->assertRedirect(route('login'));
    }

    // ---- S4 + S5: portal 2FA lockout & status ----

    public function test_suspended_portal_user_cannot_login(): void
    {
        PortalUser::factory()->create([
            'email' => 'suspended@login.test',
            'status' => 'suspended',
        ]);

        $this->post('/user/login', [
            'email' => 'suspended@login.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('portal');
    }

    public function test_portal_active_middleware_logs_out_suspended_user(): void
    {
        $user = PortalUser::factory()->create(['status' => 'inactive']);

        $this->actingAs($user, 'portal');

        $this->get('/user/dashboard')->assertRedirect(route('user.login'));
        $this->assertGuest('portal');
    }

    public function test_portal_2fa_locks_after_five_failed_attempts(): void
    {
        $user = PortalUser::factory()->create([
            'email' => '2fa@lockout.test',
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/user/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('user.2fa.challenge'));

        $this->assertGuest('portal');

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/user/2fa', ['code' => '000000'])->assertSessionHasErrors('code');
        }

        $this->post('/user/2fa', ['code' => '000000'])
            ->assertSessionHasErrors(['code' => 'Terlalu banyak percubaan. Sila log masuk semula.']);
    }

    // ---- S6: scan-commit ownership ----

    private function makeBorangImport(PortalUser $user, string $ic, string $status = 'extracted'): DocumentImport
    {
        return DocumentImport::create([
            'portal_user_id' => $user->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1000,
            'file_hash' => sha1('test-'.$user->id.'-'.$ic),
            'document_type' => 'borang_bantuan_maij',
            'status' => $status,
            'progress' => 100,
        ]);
    }

    private function makeApplication(PortalUser $user, string $ic, string $status = 'submitted'): Application
    {
        return Application::create([
            'user_id' => $user->id,
            'ic_no' => $ic,
            'full_name' => 'Ali Bin Abu',
            'phone' => '0123456789',
            'email' => $user->email,
            'household_count' => 2,
            'monthly_income' => 1200,
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }

    private function commitData(string $ic): array
    {
        return [
            'full_name' => 'Ali Bin Abu',
            'ic_no' => $ic,
            'phone' => '0123456789',
            'email' => 'ali@test.com',
            'household_count' => 2,
            'monthly_income' => 1200,
            'confirm' => '1',
        ];
    }

    public function test_scan_commit_rejects_ic_mismatching_account(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => '111111111111']);
        $import = $this->makeBorangImport($user, '111111111111');

        $response = $this->actingAs($user, 'portal')
            ->post("/user/applications/scan/{$import->id}/commit", $this->commitData('222222222222'));

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('applications', ['ic_no' => '222222222222']);
    }

    public function test_scan_commit_rejects_ic_used_by_another_applicant(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => null]);
        ProfilAsnaf::create(['nama' => 'Lain Orang', 'ic' => '333333333333', 'hubungan' => 'Ketua Keluarga']);

        $import = $this->makeBorangImport($user, '333333333333');

        $response = $this->actingAs($user, 'portal')
            ->post("/user/applications/scan/{$import->id}/commit", $this->commitData('333333333333'));

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('applications', ['ic_no' => '333333333333']);
    }

    public function test_scan_commit_rejects_short_ic(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => null]);
        $import = $this->makeBorangImport($user, '123456');

        $response = $this->actingAs($user, 'portal')
            ->post("/user/applications/scan/{$import->id}/commit", $this->commitData('123456'));

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('applications', ['ic_no' => '123456']);
    }

    // ---- B1: portal reset link ----

    public function test_portal_reset_email_links_to_portal_broker(): void
    {
        Mail::fake();

        PortalUser::factory()->create([
            'email' => 'reset@test.com',
        ]);

        $this->post('/user/forgot-password', ['email' => 'reset@test.com']);

        Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail) {
            return str_contains($mail->resetUrl, '/user/reset-password/');
        });
    }

    public function test_forgot_password_returns_uniform_response(): void
    {
        $response = $this->post('/user/forgot-password', ['email' => 'nonexistent@test.com']);

        $response->assertSessionHas('status');
        $response->assertSessionMissing('errors');
    }

    // ---- B3: status notifications ----

    public function test_approve_notifies_and_syncs_portal_user(): void
    {
        Storage::fake('local');
        Mail::fake();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $portalUser = PortalUser::factory()->create([
            'ic_no' => '900101011234',
            'email' => 'applicant-a@test.com',
        ]);

        $this->makeApplication($portalUser, '900101011234');

        $profil = ProfilAsnaf::create([
            'nama' => 'Ali Bin Abu',
            'ic' => '900101011234',
            'hubungan' => 'Ketua Keluarga',
            'pendapatan' => 1200,
            'status_kelayakan' => 'Layak',
        ]);

        app(ApprovalService::class)->approve($profil, $admin->id);

        $this->assertDatabaseHas('applications', [
            'ic_no' => '900101011234',
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $portalUser->id,
            'type' => 'application_approved',
        ]);

        Mail::assertSent(ApplicationApprovedMail::class);
    }

    public function test_submission_sends_confirmation_email_and_notification(): void
    {
        Mail::fake();

        $user = PortalUser::factory()->create([
            'ic_no' => '900101011234',
        ]);

        $application = $this->makeApplication($user, '900101011234', 'draft');

        $this->actingAs($user, 'portal')
            ->post("/user/applications/{$application->id}/submit")
            ->assertRedirect();

        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'submitted']);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'application_submitted_user',
        ]);

        Mail::assertSent(ApplicationReceivedMail::class);
    }

    // ---- B5: status wiring ----

    public function test_request_documents_marks_application_incomplete(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $portalUser = PortalUser::factory()->create(['ic_no' => '900101011234', 'email' => 'applicant-b@test.com']);

        $this->makeApplication($portalUser, '900101011234');

        ProfilAsnaf::create(['nama' => 'Ali Bin Abu', 'ic' => '900101011234', 'hubungan' => 'Ketua Keluarga']);

        $response = $this->post('/app/profilasnaf/900101011234/request-documents', [
            'documents' => ['ic', 'salary_slip'],
            'note' => 'Sila hantar dokumen sokongan.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applications', [
            'ic_no' => '900101011234',
            'status' => 'incomplete',
            'admin_remarks' => 'Sila hantar dokumen sokongan.',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $portalUser->id,
            'type' => 'documents_requested_user',
        ]);

        Mail::assertSent(AdditionalDocumentsRequestedMail::class);
    }

    public function test_admin_view_marks_submitted_application_under_review(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $portalUser = PortalUser::factory()->create(['ic_no' => '900101011234']);

        $this->makeApplication($portalUser, '900101011234');

        ProfilAsnaf::create(['nama' => 'Ali Bin Abu', 'ic' => '900101011234', 'hubungan' => 'Ketua Keluarga']);

        $this->get('/app/viewprofile/900101011234')->assertOk();

        $this->assertDatabaseHas('applications', [
            'ic_no' => '900101011234',
            'status' => 'under_review',
        ]);
    }
}
