<?php

namespace App\Jobs;

use App\Models\DocumentImport;
use App\Services\Ocr\OcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(
        public int $documentImportId
    ) {}

    public function handle(OcrService $ocr): void
    {
        $import = DocumentImport::find($this->documentImportId);

        if (! $import || ! in_array($import->status, ['processing', 'uploaded'], true)) {
            return;
        }

        if ($import->status === 'uploaded') {
            $import->update(['status' => 'processing', 'progress' => 5, 'ocr_started_at' => now()]);
        }

        try {
            $ocr->process($import);
        } catch (\Throwable $e) {
            // Keep the import in 'processing' while the job retries; only mark
            // it as permanently failed once every attempt is exhausted.
            if ($this->attempts() >= $this->tries) {
                $import->update([
                    'status' => 'failed',
                    'error_message' => $ocr->friendlyErrorMessage($e),
                    'progress' => 0,
                    'ocr_completed_at' => now(),
                ]);
            }

            throw $e;
        }
    }
}
