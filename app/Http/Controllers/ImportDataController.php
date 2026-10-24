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
                    'detail' => $h->jumlah_rekod.' rekod · '.$h->berjaya.' ok'.($h->gagal > 0 ? ' · '.$h->gagal.' gagal' : ''),
                    'status' => $h->gagal > 0 ? 'partial' : 'success',
                ])
            )
            ->concat(
                $pdfHistory->map(fn ($h) => (object) [
                    'type' => 'pdf',
                    'date' => $h->created_at,
                    'title' => $h->original_filename,
                    'detail' => 'Status: '.ucfirst($h->status).($h->overall_confidence ? ' · '.round($h->overall_confidence).'% keyakinan' : ''),
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
