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
            __('portal_documents.notifications.uploaded_title'),
            __('portal_documents.notifications.uploaded_body'),
            'fa-solid fa-cloud-arrow-up',
            'success',
            route('user.documents.index'),
        );

        return redirect()
            ->route('user.documents.index')
            ->with('success', __('portal_documents.flash.uploaded'));
    }

    public function download(string $id): StreamedResponse|RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        $path = PortalDocumentService::path($import);

        if (! file_exists($path)) {
            return back()->with('error', __('portal_documents.flash.file_not_found'));
        }

        return Storage::disk(config('ocr.disk', 'local'))->download($import->stored_path, $import->original_filename);
    }

    public function reprocess(Request $request, string $id): RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        if ($import->document_type !== OcrService::DOC_TYPE_BORANG_BANTUAN) {
            return back()->with('error', __('portal_documents.flash.reprocess_only_borang'));
        }

        if ($import->status === 'committed') {
            return back()->with('info', __('portal_documents.flash.already_committed'));
        }

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'ocr_started_at' => now(),
            'ocr_completed_at' => null,
        ]);

        ProcessDocumentImport::dispatch($import->id);

        return back()->with('success', __('portal_documents.flash.reprocessing'));
    }
}
