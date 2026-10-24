<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalLetterTest extends TestCase
{
    use RefreshDatabase;

    private function makeApplication(PortalUser $user, array $attrs = []): Application
    {
        return Application::create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Ali Bin Abu',
            'ic_no' => '900101011234',
            'phone' => '0123456789',
            'email' => $user->email,
            'status' => 'submitted',
            'submitted_at' => now(),
            'assistance_type' => 'Sara Hidup',
        ], $attrs));
    }

    public function test_receipt_requires_submitted_application(): void
    {
        $user = PortalUser::factory()->create();

        $application = $this->makeApplication($user, [
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        $this->actingAs($user, 'portal')->get(route('user.applications.receipt', $application->id))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_receipt_generates_for_submitted_application(): void
    {
        $user = PortalUser::factory()->create();

        $application = $this->makeApplication($user);

        $this->actingAs($user, 'portal')->get(route('user.applications.receipt', $application->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_cannot_access_another_users_receipt(): void
    {
        $owner = PortalUser::factory()->create();
        $other = PortalUser::factory()->create();

        $application = $this->makeApplication($owner);

        $this->actingAs($other, 'portal')->get(route('user.applications.receipt', $application->id))
            ->assertNotFound();
    }

    public function test_letters_page_lists_own_applications(): void
    {
        $user = PortalUser::factory()->create();

        $application = $this->makeApplication($user, ['assistance_type' => 'Perubatan']);

        $this->actingAs($user, 'portal')->get(route('user.letters.index'))
            ->assertOk()
            ->assertSee('Perubatan')
            ->assertSee($application->application_no);
    }
}
