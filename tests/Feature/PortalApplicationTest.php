<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_saves_assistance_type_and_documents(): void
    {
        Storage::fake('local');

        $user = PortalUser::factory()->create();

        $file = UploadedFile::fake()->create('ic.pdf', 100, 'application/pdf');

        $this->actingAs($user, 'portal')->post(route('user.applications.store'), [
            'full_name' => 'Ali Bin Abu',
            'ic_no' => '900101011234',
            'phone' => '0123456789',
            'email' => $user->email,
            'household_count' => 3,
            'monthly_income' => 1500,
            'assistance_type' => 'Sara Hidup',
            'assistance_frequency' => 'monthly',
            'ic_document' => $file,
        ])->assertRedirect();

        $application = Application::latest('id')->first();

        $this->assertEquals('Sara Hidup', $application->assistance_type);
        $this->assertEquals('monthly', $application->assistance_frequency);
        $this->assertNotNull($application->ic_document);
        $this->assertEquals('draft', $application->status);
    }

    public function test_status_page_requires_authentication(): void
    {
        $this->get(route('user.applications.status'))->assertRedirect(route('user.login'));
    }

    public function test_status_and_show_pages_render_with_documents_requested(): void
    {
        $user = PortalUser::factory()->create(['ic_no' => '900101011234']);

        $application = Application::create([
            'user_id' => $user->id,
            'full_name' => 'Ali Bin Abu',
            'ic_no' => '900101011234',
            'phone' => '0123456789',
            'email' => $user->email,
            'status' => 'under_review',
            'submitted_at' => now(),
            'assistance_type' => 'Sara Hidup',
        ]);

        ProfilAsnaf::create([
            'nama' => 'Ali Bin Abu',
            'ic' => '900101011234',
            'documents_requested_at' => now(),
            'documents_requested_list' => ['Slip Gaji'],
        ]);

        $this->actingAs($user, 'portal')->get(route('user.applications.status'))
            ->assertOk()
            ->assertSee('Dokumen tambahan diperlukan');

        $this->actingAs($user, 'portal')->get(route('user.applications.show', $application->id))
            ->assertOk();
    }
}
