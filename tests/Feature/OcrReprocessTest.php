<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentImport;
use App\Models\Admin;
use App\Models\DocumentImport;
use App\Services\Ocr\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OcrReprocessTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create([
            'email' => 'reprocess-'.uniqid().'@maij.gov.my',
        ]);
    }

    public function test_reprocess_resets_status_and_runs_job_synchronously(): void
    {
        Queue::fake();

        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'stuck.pdf',
            'stored_path' => 'ocr/stuck.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'stuck_hash',
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'status' => 'processing',
            'error_message' => 'Previous failure',
            'page_count' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.reprocess', $import->id));

        $response->assertRedirect();

        $import->refresh();
        $this->assertSame('processing', $import->status);
        $this->assertNull($import->error_message);

        Queue::assertPushed(ProcessDocumentImport::class);
    }

    public function test_reprocess_skips_committed_imports(): void
    {
        Queue::fake();

        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'done.pdf',
            'stored_path' => 'ocr/done.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'done_hash',
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'status' => 'committed',
            'page_count' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.reprocess', $import->id));

        $response->assertSessionHas('info');
        Queue::assertNotPushed(ProcessDocumentImport::class);
    }

    public function test_ocr_reprocess_command_reports_no_stuck_imports(): void
    {
        $this->artisan('ocr:reprocess', ['--all-stuck' => true])
            ->expectsOutputToContain('Tiada import perlu diproses semula.')
            ->assertExitCode(0);
    }
}
