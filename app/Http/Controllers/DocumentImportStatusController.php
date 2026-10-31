<?php

namespace App\Http\Controllers;

use App\Models\DocumentImport;

class DocumentImportStatusController extends Controller
{
    public function show(string $id)
    {
        $import = DocumentImport::findOrFail($id);

        $progress = $import->progress ?? $this->progressFor($import);
        $stalled = $import->status === 'processing'
            && $import->ocr_started_at
            && $import->ocr_started_at->diffInSeconds(now(), false) > 180;

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'progress' => (int) $progress,
            'stage' => $this->stageFor((int) $progress, $import->status),
            'overall_confidence' => $import->overall_confidence,
            'extracted_data' => $import->extracted_data,
            'error_message' => $import->error_message,
            'error' => $import->error_message,
            'extracted_count' => $import->extracted_data
                ? count(array_filter($import->extracted_data, fn ($f) => ($f['value'] ?? null) !== null))
                : 0,
            'total_fields' => count($import->extracted_data ?? []),
            'stalled' => (bool) $stalled,
            'document_type' => $import->document_type,
            'review_url' => in_array($import->status, ['extracted', 'needs_review', 'committed'])
                ? ($import->isGenericDocument() ? route('ocr.show', $import->id) : route('import-pdf.review', $import->id))
                : null,
        ]);
    }

    private function progressFor(DocumentImport $import): int
    {
        return match ($import->status) {
            'processing' => 30,
            'extracted' => 100,
            'needs_review' => 100,
            'committed' => 100,
            'failed' => 100,
            'rejected' => 100,
            default => 0,
        };
    }

    private function stageFor(int $progress, string $status): string
    {
        if (in_array($status, ['extracted', 'needs_review', 'committed'])) {
            return 'done';
        }
        if (in_array($status, ['failed', 'rejected'], true)) {
            return 'failed';
        }

        return match (true) {
            $progress >= 85 => 'recognizing',
            $progress >= 70 => 'extracted',
            $progress >= 30 => 'extracting',
            $progress >= 25 => 'analyzing',
            $progress >= 10 => 'started',
            default => 'queued',
        };
    }
}
