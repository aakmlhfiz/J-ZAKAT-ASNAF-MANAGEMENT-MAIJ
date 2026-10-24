<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AssistanceHistory;
use App\Models\AuditLog;
use App\Models\Household;
use App\Models\HouseholdMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistanceMoneyAuditTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Household $household;

    private HouseholdMember $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'web');

        $this->household = Household::create([
            'alamat' => 'Jalan Johor',
            'daerah' => 'Johor Bahru',
            'eligibility_status' => 'Layak',
        ]);

        $this->member = HouseholdMember::create([
            'household_id' => $this->household->id,
            'nama' => 'Ali Bin Abu',
            'ic' => '900101011234',
            'hubungan' => 'Ketua Isi Rumah',
        ]);

        config(['app.super_admin_emails' => [$this->admin->email]]);
    }

    public function test_store_creates_assistance_and_money_audit_log(): void
    {
        $response = $this->post(route('household.assistance.store', $this->household->id), [
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Sara Hidup',
            'amount' => 350.00,
            'frequency' => 'monthly',
            'status' => 'approved',
            'approved_date' => '2026-08-01',
            'approval_officer' => 'Ahmad',
            'reference_number' => 'REF-1001',
            'description' => 'Bantuan sara hidup bulanan',
        ]);

        $response->assertRedirect(route('household.show', $this->household->household_id));

        $this->assertDatabaseHas('assistance_histories', [
            'household_id' => $this->household->id,
            'jenis_bantuan' => 'Sara Hidup',
            'amount' => 350.00,
            'frequency' => 'monthly',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'user_role' => 'admin',
            'action' => 'money_transaction_created',
            'category' => 'money',
        ]);

        $log = AuditLog::where('action', 'money_transaction_created')->latest()->first();
        $this->assertEquals('350.00', $log->metadata['amount']);
        $this->assertEquals('Sara Hidup', $log->metadata['jenis_bantuan']);
        $this->assertEquals('REF-1001', $log->metadata['reference_number']);
    }

    public function test_update_changes_amount_and_logs_diff(): void
    {
        $history = AssistanceHistory::create([
            'household_id' => $this->household->id,
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Perubatan',
            'amount' => 100.00,
            'frequency' => 'one_off',
            'status' => 'approved',
        ]);

        $response = $this->put(route('assistance.update', $history->id), [
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Perubatan',
            'amount' => 250.00,
            'frequency' => 'one_off',
            'status' => 'approved',
            'approval_officer' => 'Siti',
        ]);

        $response->assertRedirect(route('household.show', $this->household->household_id));

        $this->assertDatabaseHas('assistance_histories', [
            'id' => $history->id,
            'amount' => 250.00,
            'approval_officer' => 'Siti',
        ]);

        $log = AuditLog::where('action', 'money_transaction_updated')->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('money', $log->category);
        $this->assertArrayHasKey('amount', $log->metadata['changes']);
        $this->assertEquals('100.00', $log->metadata['changes']['amount']['from']);
        $this->assertEquals('250.00', $log->metadata['changes']['amount']['to']);
    }

    public function test_destroy_soft_deletes_and_logs_money_deletion(): void
    {
        $history = AssistanceHistory::create([
            'household_id' => $this->household->id,
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Sewa Rumah',
            'amount' => 500.00,
            'frequency' => 'monthly',
            'status' => 'approved',
        ]);

        $response = $this->delete(route('assistance.destroy', $history->id));

        $response->assertRedirect(route('household.show', $this->household->household_id));

        $this->assertSoftDeleted('assistance_histories', ['id' => $history->id]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'money_transaction_deleted',
            'category' => 'money',
        ]);
    }

    public function test_money_audit_index_shows_stats_and_ledger(): void
    {
        AssistanceHistory::create([
            'household_id' => $this->household->id,
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'IPT',
            'amount' => 1000.00,
            'frequency' => 'one_off',
            'status' => 'approved',
            'approved_date' => now()->format('Y-m-d'),
            'reference_number' => 'REF-2001',
        ]);

        $response = $this->get(route('admin.money-audit.index'));

        $response->assertOk();
        $response->assertSee('Audit Kewangan');
        $response->assertSee('1,000.00');
        $response->assertSee('REF-2001');
        $response->assertSee('IPT');
    }

    public function test_money_audit_trail_tab_shows_money_logs(): void
    {
        AssistanceHistory::create([
            'household_id' => $this->household->id,
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Berdikari',
            'amount' => 200.00,
            'frequency' => 'one_off',
            'status' => 'approved',
        ]);

        $this->post(route('household.assistance.store', $this->household->id), [
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Berdikari',
            'amount' => 200.00,
            'frequency' => 'one_off',
            'status' => 'approved',
        ]);

        $response = $this->get(route('admin.money-audit.index', ['tab' => 'trail']));

        $response->assertOk();
        $response->assertSee('Jejak Audit');
        $response->assertSee('money_transaction_created');
    }

    public function test_money_audit_export_returns_csv(): void
    {
        AssistanceHistory::create([
            'household_id' => $this->household->id,
            'household_member_id' => $this->member->id,
            'jenis_bantuan' => 'Emergency',
            'amount' => 700.00,
            'frequency' => 'one_off',
            'status' => 'approved',
            'reference_number' => 'REF-3001',
        ]);

        $response = $this->get(route('admin.money-audit.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Emergency', $content);
        $this->assertStringContainsString('REF-3001', $content);
    }

    public function test_household_show_renders_assistance_form(): void
    {
        $response = $this->get(route('household.show', $this->household->household_id));

        $response->assertOk();
        $response->assertSee('Rekod Bantuan');
        $response->assertSee('Ali Bin Abu');
        $response->assertSee('/app/household');
    }

    public function test_money_audit_requires_super_admin(): void
    {
        config(['app.super_admin_emails' => []]);

        $response = $this->get(route('admin.money-audit.index'));

        $response->assertForbidden();
    }
}
