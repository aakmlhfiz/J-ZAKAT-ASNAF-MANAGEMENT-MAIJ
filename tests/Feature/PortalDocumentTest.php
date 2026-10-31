<?php

namespace Tests\Feature;

use App\Models\DocumentImport;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_a_document(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = PortalUser::factory()->create();

        $file = UploadedFile::fake()->create('slip-gaji.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'portal')->post(route('user.documents.store'), [
            'document_type' => 'salary',
            'file' => $file,
        ]);

        $response->assertRedirect(route('user.documents.index'));

        $this->assertDatabaseHas('document_imports', [
            'portal_user_id' => $user->id,
            'document_type' => 'salary',
            'status' => 'uploaded',
        ]);
    }

    public function test_upload_rejects_invalid_file_type(): void
    {
        Storage::fake('local');

        $user = PortalUser::factory()->create();

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $this->actingAs($user, 'portal')->post(route('user.documents.store'), [
            'document_type' => 'supporting',
            'file' => $file,
        ])->assertSessionHasErrors('file');
    }

    public function test_documents_index_lists_own_documents(): void
    {
        $user = PortalUser::factory()->create();

        DocumentImport::create([
            'portal_user_id' => $user->id,
            'original_filename' => 'ic-ahmad.pdf',
            'stored_path' => 'ocr/x.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'file_hash' => 'hash-1',
            'document_type' => 'ic',
            'status' => 'uploaded',
        ]);

        $this->actingAs($user, 'portal')->get(route('user.documents.index'))
            ->assertOk()
            ->assertSee('ic-ahmad.pdf');
    }

    public function test_user_can_download_own_document(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = PortalUser::factory()->create();

        $file = UploadedFile::fake()->create('ic.pdf', 100, 'application/pdf');

        $this->actingAs($user, 'portal')->post(route('user.documents.store'), [
            'document_type' => 'ic',
            'file' => $file,
        ]);

        $doc = DocumentImport::where('portal_user_id', $user->id)->firstOrFail();

        $this->actingAs($user, 'portal')->get(route('user.documents.download', $doc->id))
            ->assertOk();
    }

    public function test_user_cannot_download_another_users_document(): void
    {
        Storage::fake('local');

        $owner = PortalUser::factory()->create();
        $other = PortalUser::factory()->create();

        $doc = DocumentImport::create([
            'portal_user_id' => $owner->id,
            'original_filename' => 'private.pdf',
            'stored_path' => 'ocr/private.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'file_hash' => 'hash-2',
            'document_type' => 'supporting',
            'status' => 'uploaded',
        ]);

        $this->actingAs($other, 'portal')->get(route('user.documents.download', $doc->id))
            ->assertNotFound();
    }
}
