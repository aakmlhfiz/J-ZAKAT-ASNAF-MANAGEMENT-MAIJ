<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_malay_is_the_default_locale(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Selamat kembali');
    }

    public function test_guest_can_switch_to_english(): void
    {
        $this->post(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Log In');
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->post(route('locale.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');
    }

    public function test_authenticated_user_language_is_persisted(): void
    {
        $admin = Admin::factory()->create(['language' => 'ms']);

        $this->actingAs($admin, 'web')
            ->post(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect();

        $this->assertSame('en', $admin->fresh()->language);
        $this->assertSame('en', session('locale'));
    }

    public function test_user_language_preference_is_used_without_a_session_locale(): void
    {
        $admin = Admin::factory()->create(['language' => 'en']);

        $this->actingAs($admin, 'web')
            ->get('/login')
            ->assertOk()
            ->assertSee('Welcome back');
    }

    public function test_dashboard_and_navigation_are_translated(): void
    {
        $admin = Admin::factory()->create(['language' => 'en']);

        $this->actingAs($admin, 'web')
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total Applicants')
            ->assertSee('Latest Applications')
            ->assertSee('Asnaf List')
            ->assertSee('Reports');
    }
}
