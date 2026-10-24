<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'logintest@j-zakat.test',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated('web');
    }

    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'badlogin@j-zakat.test',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('web');
    }

    public function test_admin_cannot_login_with_empty_fields(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest('web');
    }

    public function test_admin_with_2fa_is_challenged_after_login(): void
    {
        $admin = Admin::factory()->create([
            'email' => '2fa@j-zakat.test',
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('auth.2fa.challenge'));
        $this->assertGuest('web');
    }

    public function test_2fa_challenge_redirects_to_login_without_session(): void
    {
        $response = $this->get('/2fa');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_logout(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'logout@j-zakat.test',
        ]);

        $this->actingAs($admin, 'web');

        $response = $this->post('/logout');

        $response->assertRedirect();
        $this->assertGuest('web');
    }

    public function test_portal_login_page_loads(): void
    {
        $response = $this->get('/user/login');
        $response->assertStatus(200);
    }

    public function test_portal_user_can_login_with_valid_credentials(): void
    {
        $user = PortalUser::factory()->create([
            'email' => 'portal-login@j-zakat.test',
        ]);

        $response = $this->post('/user/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticated('portal');
    }

    public function test_portal_user_cannot_login_with_invalid_credentials(): void
    {
        PortalUser::factory()->create([
            'email' => 'portal-bad@j-zakat.test',
        ]);

        $response = $this->post('/user/login', [
            'email' => 'portal-bad@j-zakat.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('portal');
    }

    public function test_unauthenticated_portal_request_redirects_to_portal_login(): void
    {
        $response = $this->get('/user/dashboard');

        $response->assertRedirect(route('user.login'));
    }
}
