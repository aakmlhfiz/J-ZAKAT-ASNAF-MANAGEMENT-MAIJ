<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Jobs\ProcessDocumentImport;
use App\Models\DocumentImport;
use App\Models\PortalUser;
use App\Services\Document\DocumentFieldSchema;
use App\Services\Document\DocumentPreviewService;
use App\Services\Document\DocumentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OcrManagementController extends Controller
{
    public function __construct(
        protected DocumentPreviewService $preview,
        protected DocumentVerificationService $verification,
    ) {}

    public function index(Request $request): View
    {
        $query = DocumentImport::query()->with(['user', 'portalUser', 'profilAsnaf']);

        if ($search = trim((string) $request->get('search'))) {
            $portalIds = PortalUser::query()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->pluck('id')
                ->all();

            $query->where(function ($q) use ($search, $portalIds) {
                $q->where('original_filename', 'like', "%{$search}%")
                    ->orWhere('profil_asnaf_ic', 'like', "%{$search}%");
                if ($portalIds !== []) {
                    $q->orWhereIn('portal_user_id', $portalIds);
                }
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $legacy = match ($type) {
                'mykad' => ['mykad', 'ic'],
                'salary_slip' => ['salary_slip', 'salary'],
                'other' => ['other', 'supporting'],
                default => [$type],
            };
            $query->whereIn('document_type', $legacy);
        }

        $items = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total' => DocumentImport::count(),
            'uploaded' => DocumentImport::where('status', 'uploaded')->count(),
            'processing' => DocumentImport::where('status', 'processing')->count(),
            'awaiting_verification' => DocumentImport::whereIn('status', ['extracted', 'needs_review'])->count(),
            'verified' => DocumentImport::where('status', 'committed')->count(),
            'failed' => DocumentImport::whereIn('status', ['failed', 'rejected'])->count(),
        ];

        $types = [];
        foreach (DocumentType::cases() as $type) {
            $types[$type->value] = $type->label();
        }
        // Keep legacy values selectable so old records are still discoverable.
        $types['ic'] = DocumentType::IdentityCard->label();
        $types['salary'] = DocumentType::SalarySlip->label();
        $types['supporting'] = DocumentType::OtherDocument->label();

        return view('ocr.index', compact('items', 'stats', 'types'));
    }

    public function show(string $id): View|RedirectResponse
    {
        $import = DocumentImport::with(['user', 'portalUser', 'profilAsnaf', 'application', 'household', 'verifiedValues'])
            ->findOrFail($id);

        \App\Services\AuditLogger::documentAccessed(auth('web')->user(), $import, request(), 'document.viewed');

        if ($import->type()->isBorang()) {
            return redirect()->route('import-pdf.review', $import->id);
        }

        return view('ocr.review', [
            'import' => $import,
            'type' => $import->type(),
            'groups' => DocumentFieldSchema::groups($import->type()),
            'confidence' => $import->overall_confidence,
            'previewPageUrl' => route('ocr.page', [$import->id, '__PAGE__']),
            'verifyRoute' => route('ocr.verify', $import->id),
            'rejectRoute' => route('ocr.reject', $import->id),
            'fileRoute' => route('ocr.file', $import->id),
            'backRoute' => route('ocr.index'),
        ]);
    }

    public function verify(Request $request, string $id): RedirectResponse
    {
        $admin = auth('web')->user();
        $import = DocumentImport::findOrFail($id);

        if (! $import->isGenericDocument()) {
            return redirect()->route('import-pdf.review', $import->id);
        }

        $result = $this->verification->verify($import, $request->all(), $admin, $request);

        return redirect()
            ->route('ocr.show', $import->id)
            ->with('success', __('document_ocr.review.verified_text'))
            ->with('verification_skipped', $result['skipped']);
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $admin = auth('web')->user();
        $import = DocumentImport::findOrFail($id);

        if ($import->status === 'committed') {
            return back()->with('error', __('imports.messages.pdf_already_committed'));
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $this->verification->reject($import, $validated['rejection_reason'], $admin, $request);

        return redirect()
            ->route('ocr.show', $import->id)
            ->with('success', __('document_ocr.admin.rejected_text'));
    }

    public function reprocess(string $id): RedirectResponse
    {
        $import = DocumentImport::findOrFail($id);

        if ($import->status === 'committed') {
            return back()->with('info', __('imports.messages.pdf_already_committed'));
        }

        $this->preview->clear($import);
        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'ocr_started_at' => now(),
            'ocr_completed_at' => null,
            'progress' => 0,
        ]);

        ProcessDocumentImport::dispatch($import->id);

        return back()->with('success', __('imports.messages.pdf_reprocessing'));
    }

    public function page(string $id, int $page): BinaryFileResponse
    {
        $import = DocumentImport::findOrFail($id);
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

    public function file(string $id)
    {
        $import = DocumentImport::findOrFail($id);

        if (! $import->fileExists()) {
            abort(404, __('imports.messages.pdf_file_missing'));
        }

        \App\Services\AuditLogger::documentAccessed(auth('web')->user(), $import, request(), 'document.downloaded');

        return response()->file(
            Storage::disk(config('ocr.disk', 'local'))->path($import->stored_path),
            [
                'Content-Type' => $import->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
