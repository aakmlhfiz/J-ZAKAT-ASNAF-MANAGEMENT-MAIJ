<?php

namespace App\Http\Controllers;

use App\Models\DocumentImport;
use Illuminate\View\View;

class ImportDataController extends Controller
{
    public function index(): View
    {
        $pdfStats = [
            'total' => DocumentImport::count(),
            'extracted' => DocumentImport::whereIn('status', ['extracted', 'needs_review'])->count(),
            'committed' => DocumentImport::where('status', 'committed')->count(),
            'failed' => DocumentImport::whereIn('status', ['failed', 'rejected'])->count(),
        ];
        $pdfHistory = DocumentImport::latest()->limit(20)->get();

        $combinedHistory = $pdfHistory
            ->map(fn ($h) => (object) [
                'type' => 'pdf',
                'date' => $h->created_at,
                'title' => $h->original_filename,
                'detail' => __('imports.messages.pdf_detail', ['status' => match ($h->status) {
                    'extracted' => __('imports.status.extracted'),
                    'needs_review' => __('imports.status.needs_review'),
                    'committed' => __('imports.status.committed'),
                    'failed' => __('imports.status.failed'),
                    'rejected' => __('document_ocr.status.rejected'),
                    'processing' => __('imports.status.processing'),
                    default => $h->status,
                }]).($h->overall_confidence ? __('imports.messages.pdf_detail_confidence', ['confidence' => round($h->overall_confidence)]) : ''),
                'status' => match ($h->status) {
                    'extracted', 'needs_review', 'committed' => 'success',
                    'failed', 'rejected' => 'danger',
                    default => 'warning',
                },
                'url' => in_array($h->status, ['extracted', 'needs_review'], true)
                    ? route('import-pdf.preview', $h->id)
                    : null,
            ])
            ->sortByDesc('date')
            ->take(15)
            ->values();

        return view('import-data', compact('pdfStats', 'pdfHistory', 'combinedHistory'));
    }
}
