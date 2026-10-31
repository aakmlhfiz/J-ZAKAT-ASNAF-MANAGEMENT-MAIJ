<?php

namespace App\Http\Controllers\Portal;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentImport;
use App\Models\Application;
use App\Models\DocumentImport;
use App\Services\AuditLogger;
use App\Services\Document\DocumentPreviewService;
use App\Services\Document\DocumentVerificationService;
use App\Services\NotificationService;
use App\Services\Ocr\OcrService;
use App\Services\PortalDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentPreviewService $preview,
        protected DocumentVerificationService $verification,
    ) {}

    public function index(): View
    {
        $user = auth('portal')->user();

        $documents = DocumentImport::where('portal_user_id', $user->id)
            ->latest()
            ->paginate(15);

        $applications = Application::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'incomplete'])
            ->latest()
            ->get(['id', 'application_no', 'status']);

        return view('portal.documents.index', compact('user', 'documents', 'applications'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth('portal')->user();
        $maxSize = (int) config('ocr.max_file_size_kb', 10240);

        $validated = $request->validate([
            'document_type' => ['required', 'in:'.implode(',', DocumentType::acceptedUploadValues())],
            'application_id' => ['nullable', 'integer'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:'.$maxSize],
        ]);

        $application = null;
        if (! empty($validated['application_id'])) {
            $application = Application::where('user_id', $user->id)
                ->whereIn('status', ['draft', 'submitted', 'under_review', 'incomplete'])
                ->find($validated['application_id']);
            if (! $application) {
                return back()->withErrors(['application_id' => __('document_ocr.validation.invalid', ['field' => __('document_ocr.admin.applicant')])])->withInput();
            }
        }

        $file = $request->file('file');
        $hash = sha1_file($file->getRealPath());
        $duplicate = PortalDocumentService::findDuplicate($user, $hash);

        if ($duplicate) {
            $url = $duplicate->isGenericDocument()
                ? route('user.documents.review', $duplicate->id)
                : route('user.applications.scan.preview', $duplicate->id);

            if ($request->wantsJson()) {
                return response()->json([
                    'duplicate' => true,
                    'document_id' => $duplicate->id,
                    'status_url' => route('user.documents.status', $duplicate->id),
                    'review_url' => $url,
                ]);
            }

            return redirect($url)->with('info', __('portal_documents.flash.duplicate'));
        }

        $import = PortalDocumentService::store(
            $user,
            $file,
            $validated['document_type'],
            $application?->id,
        );

        AuditLogger::documentUploaded($user, $import, $request);

        ProcessDocumentImport::dispatch($import->id);

        NotificationService::notifyUsers(
            [$user->id],
            'document_uploaded_user',
            __('portal_documents.notifications.uploaded_title'),
            __('portal_documents.notifications.uploaded_body'),
            'fa-solid fa-cloud-arrow-up',
            'success',
            route('user.documents.index'),
        );

        $reviewUrl = route('user.documents.review', $import->id);

        if ($request->wantsJson()) {
            return response()->json([
                'document_id' => $import->id,
                'status_url' => route('user.documents.status', $import->id),
                'review_url' => $reviewUrl,
                'duplicate' => false,
            ]);
        }

        return redirect()
            ->route('user.documents.index')
            ->with('success', __('portal_documents.flash.uploaded'));
    }

    public function review(Request $request, string $id): View|RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        AuditLogger::documentAccessed($user, $import, $request ?? request(), 'document.viewed');

        if ($import->type()->isBorang()) {
            return redirect()->route('user.applications.scan.preview', $import->id);
        }

        if (! in_array($import->status, ['needs_review', 'rejected', 'failed', 'committed'], true)) {
            return redirect()->route('user.documents.index')
                ->with('info', __('document_ocr.status.processing'));
        }

        return view('portal.documents.review', [
            'import' => $import,
            'type' => $import->type(),
            'groups' => \App\Services\Document\DocumentFieldSchema::groups($import->type()),
            'confidence' => $import->overall_confidence,
            'previewPageUrl' => route('user.documents.page', [$import->id, '__PAGE__']),
            'verifyRoute' => route('user.documents.verify', $import->id),
            'fileRoute' => route('user.documents.download', $import->id),
            'backRoute' => route('user.documents.index'),
        ]);
    }

    public function verify(Request $request, string $id): RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        if (! $import->isGenericDocument()) {
            return back()->with('error', __('document_ocr.validation.fields_invalid'));
        }

        $result = $this->verification->verify($import, $request->all(), $user, $request);

        return redirect()
            ->route('user.documents.review', $import->id)
            ->with('success', __('document_ocr.review.verified_text'))
            ->with('verification_skipped', $result['skipped']);
    }

    public function status(string $id): JsonResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'status_label' => $import->statusLabel(),
            'progress' => (int) ($import->progress ?? 0),
            'error' => $import->error_message,
            'review_url' => $import->isGenericDocument()
                ? route('user.documents.review', $import->id)
                : route('user.applications.scan.preview', $import->id),
        ]);
    }

    public function page(string $id, int $page): BinaryFileResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        $path = $this->preview->pagePath($import, $page);
        abort_if($path === null, 404);

        return response()->file($path, [
            'Content-Type' => str_starts_with((string) $import->mime_type, 'image/')
                ? $import->mime_type
                : 'image/png',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(string $id): StreamedResponse|RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        if (! $import->fileExists()) {
            return back()->with('error', __('portal_documents.flash.file_not_found'));
        }

        AuditLogger::documentAccessed($user, $import, request(), 'document.downloaded');

        return Storage::disk(config('ocr.disk', 'local'))->download($import->stored_path, $import->original_filename);
    }

    public function replace(Request $request, string $id): RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        if ($import->status === 'committed') {
            return back()->with('error', __('portal_documents.flash.already_committed'));
        }

        $maxSize = (int) config('ocr.max_file_size_kb', 10240);
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:'.$maxSize],
        ]);

        $file = $request->file('file');
        $disk = config('ocr.disk', 'local');
        $ext = strtolower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs(config('ocr.path', 'ocr'), \Illuminate\Support\Str::uuid().'.'.$ext, $disk);

        if (! $storedPath) {
            return back()->with('error', __('portal_documents.flash.file_not_found'));
        }

        if ($import->stored_path && Storage::disk($disk)->exists($import->stored_path)) {
            Storage::disk($disk)->delete($import->stored_path);
        }

        $this->preview->clear($import);

        $import->update([
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => sha1_file($file->getRealPath()),
            'status' => 'uploaded',
            'progress' => 0,
            'ocr_text' => null,
            'ocr_engine' => null,
            'extracted_data' => null,
            'ocr_result' => null,
            'overall_confidence' => null,
            'error_message' => null,
            'ocr_started_at' => null,
            'ocr_completed_at' => null,
            'verified_at' => null,
            'verified_by_portal_user_id' => null,
            'verified_data' => null,
            'metadata' => array_merge($import->metadata ?? [], ['replaced_at' => now()->toIso8601String()]),
        ]);

        ProcessDocumentImport::dispatch($import->id);

        return redirect()
            ->route('user.documents.index')
            ->with('success', __('portal_documents.flash.reprocessing'));
    }

    public function reprocess(Request $request, string $id): RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)->findOrFail($id);

        if ($import->status === 'committed') {
            return back()->with('info', __('portal_documents.flash.already_committed'));
        }

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'ocr_started_at' => now(),
            'ocr_completed_at' => null,
            'progress' => 0,
        ]);

        ProcessDocumentImport::dispatch($import->id);

        return back()->with('success', __('portal_documents.flash.reprocessing'));
    }
}
