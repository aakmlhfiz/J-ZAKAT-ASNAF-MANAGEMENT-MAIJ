<?php

namespace Tests\Feature;

use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_household_page_shows_members_and_summary(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => '900101011234']);

        $household = Household::create([
            'household_id' => 'HR-2026-TEST1',
            'total_income' => 2500.00,
            'total_expenses' => 600.00,
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'nama' => 'Ahmad bin Ali',
            'ic' => '900101011234',
            'hubungan' => 'Ketua Isi Rumah',
            'umur' => 35,
            'pendapatan_bulanan' => 1500.00,
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'nama' => 'Siti binti Ahmad',
            'ic' => '900202012345',
            'hubungan' => 'Isteri',
            'umur' => 32,
            'pendapatan_bulanan' => 1000.00,
        ]);

        $this->actingAs($user, 'portal')->get(route('user.household'))
            ->assertOk()
            ->assertSee('Ahmad bin Ali')
            ->assertSee('Siti binti Ahmad')
            ->assertSee('2');
    }

    public function test_household_page_empty_state_without_household(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => '900101011234']);

        $this->actingAs($user, 'portal')->get(route('user.household'))
            ->assertOk()
            ->assertSee('Tiada rekod isi rumah');
    }

    public function test_assistance_page_lists_approved_assistance(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => '900101011234']);

        $household = Household::create(['household_id' => 'HR-2026-TEST2']);

        $member = HouseholdMember::create([
            'household_id' => $household->id,
            'nama' => 'Ahmad bin Ali',
            'ic' => '900101011234',
            'hubungan' => 'Ketua Isi Rumah',
        ]);

        AssistanceHistory::create([
            'household_id' => $household->id,
            'household_member_id' => $member->id,
            'jenis_bantuan' => 'Sara Hidup',
            'amount' => 500.00,
            'frequency' => 'monthly',
            'status' => 'approved',
            'approved_date' => now(),
        ]);

        $this->actingAs($user, 'portal')->get(route('user.household.assistance'))
            ->assertOk()
            ->assertSee('Sara Hidup');
    }

    public function test_finance_page_requires_household_for_summary(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => '900101011234']);

        $this->actingAs($user, 'portal')->get(route('user.household.finance'))
            ->assertOk()
            ->assertSee('Tiada maklumat kewangan');
    }
}
