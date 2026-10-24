<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentImport;
use App\Models\DocumentImport;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use App\Services\Ocr\OcrService;
use App\Services\PortalDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): View
    {
        $user = auth('portal')->user();

        $documents = DocumentImport::where('portal_user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('portal.documents.index', compact('user', 'documents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth('portal')->user();
        $maxSize = (int) config('ocr.max_file_size_kb', 10240);

        $validated = $request->validate([
            'document_type' => ['required', 'in:'.implode(',', array_keys(PortalDocumentService::typeOptions()))],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.$maxSize],
        ]);

        $import = PortalDocumentService::store($user, $request->file('file'), $validated['document_type']);

        AuditLogger::documentUploaded($user, $import, $request);

        NotificationService::notifyUsers(
            [$user->id],
            'document_uploaded_user',
            'Dokumen Dimuat Naik',
            'Dokumen anda telah berjaya dimuat naik.',
            'fa-solid fa-cloud-arrow-up',
            'success',
            route('user.documents.index'),
        );

        return redirect()
            ->route('user.documents.index')
            ->with('success', 'Dokumen berjaya dimuat naik.');
    }

    public function download(string $id): StreamedResponse|RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        $path = PortalDocumentService::path($import);

        if (! file_exists($path)) {
            return back()->with('error', 'Fail tidak dijumpai pada storan.');
        }

        return Storage::disk(config('ocr.disk', 'local'))->download($import->stored_path, $import->original_filename);
    }

    public function reprocess(Request $request, string $id): RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        if ($import->document_type !== OcrService::DOC_TYPE_BORANG_BANTUAN) {
            return back()->with('error', 'Hanya Borang Bantuan MAIJ boleh diproses semula.');
        }

        if ($import->status === 'committed') {
            return back()->with('info', 'Rekod ini telah dihantar — tidak perlu diproses semula.');
        }

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'ocr_started_at' => now(),
            'ocr_completed_at' => null,
        ]);

        ProcessDocumentImport::dispatch($import->id);

        return back()->with('success', 'Dokumen sedang diproses semula.');
    }
}
