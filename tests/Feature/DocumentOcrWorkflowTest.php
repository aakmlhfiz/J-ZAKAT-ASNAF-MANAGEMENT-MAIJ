<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentImport;
use App\Models\Admin;
use App\Models\DocumentImport;
use App\Models\DocumentVerifiedValue;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentOcrWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeDocument(PortalUser $user, array $overrides = []): DocumentImport
    {
        $fields = [
            'full_name' => ['value' => 'ALI BIN ABU', 'confidence' => null, 'confidence_source' => null, 'needs_review' => true, 'page' => 1],
            'ic_number' => ['value' => '900101011234', 'confidence' => null, 'confidence_source' => null, 'needs_review' => true, 'page' => 1],
            'address' => ['value' => '12 Jalan Mawar', 'confidence' => null, 'confidence_source' => null, 'needs_review' => true, 'page' => 1],
            'postcode' => ['value' => '80100', 'confidence' => null, 'confidence_source' => null, 'needs_review' => true, 'page' => 1],
            'state' => ['value' => 'Johor', 'confidence' => null, 'confidence_source' => null, 'needs_review' => true, 'page' => 1],
        ];

        return DocumentImport::create(array_merge([
            'portal_user_id' => $user->id,
            'original_filename' => 'mykad.pdf',
            'stored_path' => 'ocr/mykad.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'file_hash' => uniqid('hash_', true),
            'document_type' => 'mykad',
            'template' => 'MyKad',
            'status' => 'needs_review',
            'page_count' => 1,
            'extracted_data' => $fields,
            'ocr_result' => [
                'document_type' => 'mykad',
                'status' => 'awaiting_verification',
                'provider' => 'pdfparser',
                'extracted_data' => $fields,
                'confidence' => array_map(fn ($field) => $field['confidence'], $fields),
                'warnings' => [],
                'meta' => ['page_count' => 1],
            ],
        ], $overrides));
    }

    public function test_portal_upload_creates_a_document_and_queues_ocr(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = PortalUser::factory()->create();
        $file = UploadedFile::fake()->create('mykad.png', 100, 'image/png');

        $this->actingAs($user, 'portal')
            ->post(route('user.documents.store'), [
                'document_type' => 'mykad',
                'file' => $file,
            ])
            ->assertRedirect(route('user.documents.index'));

        $document = DocumentImport::where('portal_user_id', $user->id)->firstOrFail();

        $this->assertSame('uploaded', $document->status);
        $this->assertSame('mykad', $document->document_type);
        Queue::assertPushed(ProcessDocumentImport::class, fn ($job) => $job->documentImportId === $document->id);
    }

    public function test_duplicate_upload_returns_the_existing_document(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = PortalUser::factory()->create();
        $file = UploadedFile::fake()->create('mykad.png', 100, 'image/png');

        $this->actingAs($user, 'portal')->post(route('user.documents.store'), [
            'document_type' => 'mykad',
            'file' => $file,
        ]);

        $this->actingAs($user, 'portal')->post(route('user.documents.store'), [
            'document_type' => 'mykad',
            'file' => UploadedFile::fake()->createWithContent('mykad.png', file_get_contents($file->getRealPath())),
        ]);

        $this->assertSame(1, DocumentImport::where('portal_user_id', $user->id)->count());
    }

    public function test_applicant_can_only_access_their_own_review(): void
    {
        $owner = PortalUser::factory()->create();
        $other = PortalUser::factory()->create();
        $document = $this->makeDocument($owner);

        $this->actingAs($owner, 'portal')->get(route('user.documents.review', $document->id))->assertOk();
        $this->actingAs($other, 'portal')->get(route('user.documents.review', $document->id))->assertNotFound();
    }

    public function test_applicant_can_correct_and_verify_extracted_values(): void
    {
        $user = PortalUser::factory()->create(['name' => 'OLD NAME', 'address' => null]);
        $document = $this->makeDocument($user);

        $this->actingAs($user, 'portal')
            ->post(route('user.documents.verify', $document->id), [
                'fields' => [
                    'full_name' => 'ALI BIN ABU',
                    'ic_number' => '900101011234',
                    'address' => '12 Jalan Mawar',
                    'postcode' => '80100',
                    'state' => 'Johor',
                ],
                'apply_profile' => '1',
                'replace_existing' => '1',
            ])
            ->assertRedirect(route('user.documents.review', $document->id));

        $document->refresh();
        $this->assertSame('committed', $document->status);
        $this->assertNotNull($document->verified_at);
        $this->assertSame($user->id, $document->verified_by_portal_user_id);
        $this->assertDatabaseHas('document_verified_values', [
            'document_import_id' => $document->id,
            'field_key' => 'full_name',
            'value_text' => 'ALI BIN ABU',
        ]);
        $this->assertSame('ALI BIN ABU', $user->fresh()->name);
    }

    public function test_officer_can_view_reject_and_verify_documents(): void
    {
        $admin = Admin::factory()->create();
        $user = PortalUser::factory()->create();
        $document = $this->makeDocument($user);

        $this->actingAs($admin, 'web')->get(route('ocr.index'))->assertOk();
        $this->actingAs($admin, 'web')->get(route('ocr.show', $document->id))->assertOk();

        $this->actingAs($admin, 'web')
            ->post(route('ocr.reject', $document->id), ['rejection_reason' => 'Dokumen tidak jelas'])
            ->assertRedirect(route('ocr.show', $document->id));

        $this->assertSame('rejected', $document->fresh()->status);

        $second = $this->makeDocument($user, ['original_filename' => 'mykad-2.pdf']);
        $this->actingAs($admin, 'web')
            ->post(route('ocr.verify', $second->id), [
                'fields' => [
                    'full_name' => 'ALI BIN ABU',
                    'ic_number' => '900101011234',
                    'address' => '12 Jalan Mawar',
                    'postcode' => '80100',
                    'state' => 'Johor',
                ],
            ])
            ->assertRedirect(route('ocr.show', $second->id));

        $this->assertSame('committed', $second->fresh()->status);
        $this->assertSame($admin->id, $second->fresh()->verified_by_admin_id);
        $this->assertTrue(DocumentVerifiedValue::where('document_import_id', $second->id)->exists());
    }

    public function test_security_limits_reject_oversized_files(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = PortalUser::factory()->create();
        $file = UploadedFile::fake()->create('oversized.pdf', 11000, 'application/pdf');

        $this->actingAs($user, 'portal')
            ->post(route('user.documents.store'), [
                'document_type' => 'mykad',
                'file' => $file,
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, DocumentImport::where('portal_user_id', $user->id)->count());
    }
}
