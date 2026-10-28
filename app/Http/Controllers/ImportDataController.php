<?php

namespace App\Http\Controllers;

use App\Models\DocumentImport;
use App\Models\ImportHistory;
use Illuminate\View\View;

class ImportDataController extends Controller
{
    public function index(): View
    {
        $csvStats = [
            'total' => ImportHistory::count(),
            'berjaya' => ImportHistory::sum('berjaya'),
            'gagal' => ImportHistory::sum('gagal'),
        ];
        $csvHistory = ImportHistory::latest()->limit(10)->get();

        $pdfStats = [
            'total' => DocumentImport::count(),
            'extracted' => DocumentImport::where('status', 'extracted')->count(),
            'committed' => DocumentImport::where('status', 'committed')->count(),
            'failed' => DocumentImport::where('status', 'failed')->count(),
        ];
        $pdfHistory = DocumentImport::latest()->limit(10)->get();

        $combinedHistory = collect()
            ->concat(
                $csvHistory->map(fn ($h) => (object) [
                    'type' => 'csv',
                    'date' => $h->created_at,
                    'title' => $h->nama_fail,
                    'detail' => __('imports.messages.csv_detail', ['records' => $h->jumlah_rekod, 'success' => $h->berjaya])
                        .($h->gagal > 0 ? __('imports.messages.csv_detail_failed', ['failed' => $h->gagal]) : ''),
                    'status' => $h->gagal > 0 ? 'partial' : 'success',
                ])
            )
            ->concat(
                $pdfHistory->map(fn ($h) => (object) [
                    'type' => 'pdf',
                    'date' => $h->created_at,
                    'title' => $h->original_filename,
                    'detail' => __('imports.messages.pdf_detail', ['status' => match ($h->status) {
                        'extracted' => __('imports.status.extracted'),
                        'needs_review' => __('imports.status.needs_review'),
                        'committed' => __('imports.status.committed'),
                        'failed' => __('imports.status.failed'),
                        'processing' => __('imports.status.processing'),
                        default => $h->status,
                    }]).($h->overall_confidence ? __('imports.messages.pdf_detail_confidence', ['confidence' => round($h->overall_confidence)]) : ''),
                    'status' => match ($h->status) {
                        'extracted', 'committed' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    },
                    'url' => $h->status === 'extracted' ? route('import-pdf.preview', $h->id) : null,
                ])
            )
            ->sortByDesc('date')
            ->take(15)
            ->values();

        return view('import-data', compact('csvStats', 'csvHistory', 'pdfStats', 'pdfHistory', 'combinedHistory'));
    }
}
