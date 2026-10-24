<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentImport;
use App\Models\DocumentImport;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalScanUploadTest extends TestCase
{
    use RefreshDatabase;

    private PortalUser $portalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupPortalUserTable();

        $this->portalUser = PortalUser::factory()->create([
            'email' => 'pemohon-'.uniqid().'@maij.gov.my',
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
        }
    }

    public function test_upload_redirects_to_preview_and_dispatches_async_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('borang-bantuan-MAIJ.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->portalUser, 'portal')
            ->post(route('user.applications.scan.upload'), ['file' => $file]);

        $import = DocumentImport::where('portal_user_id', $this->portalUser->id)->first();

        $this->assertNotNull($import, 'A DocumentImport record should be created');
        $this->assertSame('processing', $import->status);

        $response->assertRedirect(route('user.applications.scan.preview', $import->id));

        Queue::assertPushed(ProcessDocumentImport::class, fn ($job) => $job->documentImportId === $import->id);
    }

    public function test_upload_rejects_non_supported_file_types(): void
    {
        Queue::fake();

        $file = UploadedFile::fake()->create('not-a-form.txt', 100, 'text/plain');

        $response = $this->actingAs($this->portalUser, 'portal')
            ->post(route('user.applications.scan.upload'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, DocumentImport::where('portal_user_id', $this->portalUser->id)->count());
        Queue::assertNothingPushed();
    }
}