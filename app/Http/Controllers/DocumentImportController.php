<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentImport;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\DocumentImport;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\ImportHistory;
use App\Models\ProfilAsnaf;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\ImportDuplicateDetectionService;
use App\Services\ImportHouseholdLookupService;
use App\Services\ImportValidationService;
use App\Services\NotificationService;
use App\Services\Ocr\Extractors\MistralOcrExtractor;
use App\Services\Ocr\Extractors\TesseractExtractor;
use App\Services\Ocr\OcrService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentImportController extends Controller
{
    public function __construct(
        protected OcrService $ocr,
    ) {}

    public function index(): View
    {
        $history = DocumentImport::latest()->limit(10)->get();

        $stats = [
            'total' => DocumentImport::count(),
            'extracted' => DocumentImport::whereIn('status', ['extracted', 'needs_review'])->count(),
            'committed' => DocumentImport::where('status', 'committed')->count(),
            'failed' => DocumentImport::where('status', 'failed')->count(),
            'processing' => DocumentImport::where('status', 'processing')->count(),
        ];

        $csvHistory = ImportHistory::latest()->limit(10)->get();
        $csvStats = [
            'total' => ImportHistory::sum('jumlah_rekod') ?: 0,
            'berjaya' => ImportHistory::sum('berjaya') ?: 0,
            'gagal' => ImportHistory::sum('gagal') ?: 0,
        ];

        return view('import-pdf.index', compact('history', 'stats', 'csvHistory', 'csvStats'));
    }

    /**
     * Serve the blank MAIJ borang template PDF for download/printing. Gated by
     * the web guard; downloads are audit-logged.
     */
    public function template(Request $request)
    {
        $path = (string) config('ocr.document_intelligence.template_pdf');

        if (! $path || ! file_exists($path)) {
            abort(404, __('imports.messages.pdf_template_missing'));
        }

        ActivityLog::create([
            'user_id' => auth('web')->id(),
            'activity' => 'ocr_template_downloaded',
            'description' => __('imports.messages.pdf_template_activity', ['file' => basename($path)]),
            'ip_address' => $request->ip(),
        ]);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }

    public function preflight(): JsonResponse
    {
        $tesseract = TesseractExtractor::isAvailable();
        $imagick = class_exists(\Imagick::class);
        $ocrMyPdf = (bool) config('ocr.ocr_my_pdf.enabled', false);
        $mistral = MistralOcrExtractor::isConfigured();
        $engine = config('ocr.default', 'hybrid');

        $stalledCount = DocumentImport::where('status', 'processing')
            ->where('ocr_started_at', '<', now()->subSeconds(180))
            ->count();

        $pendingQueue = DB::table('jobs')->count();

        return response()->json([
            'paddle_available' => false,
            'tesseract_available' => $tesseract,
            'imagick_available' => $imagick,
            'ocr_my_pdf_available' => $ocrMyPdf,
            'mistral_configured' => $mistral,
            'engine_label' => $engine,
            'any_engine_ready' => $tesseract || $imagick || $ocrMyPdf || $mistral,
            'stalled_count' => $stalledCount,
            'pending_queue' => $pendingQueue,
            'queue_running' => $pendingQueue === 0,
        ]);
    }

    public function upload(Request $request): RedirectResponse|JsonResponse
    {
        set_time_limit((int) config('ocr.limits.sync_request_timeout_seconds', 60));
        ini_set('memory_limit', '512M');

        $maxSize = (int) config('ocr.max_file_size_kb', 10240);

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:'.$maxSize,
            ],
            'document_type' => [
                'nullable',
                'string',
                'in:generic,borang_bantuan_maij',
            ],
        ], [
            'file.required' => __('imports.messages.pdf_file_required'),
            'file.mimes' => __('imports.messages.pdf_file_mimes'),
            'file.max' => __('imports.messages.pdf_file_max', ['size' => $maxSize / 1024]),
            'document_type.in' => __('imports.messages.pdf_document_type_invalid'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $hash = sha1_file($file->getRealPath());
        $documentType = $request->input('document_type', 'generic');

        $duplicate = DocumentImport::where('file_hash', $hash)->first();

        if ($duplicate) {
            if (in_array($duplicate->status, ['extracted', 'needs_review'])) {
                $route = route('import-pdf.preview', $duplicate->id);

                return back()->with('info', __('imports.messages.pdf_duplicate_info', ['url' => $route]));
            }

            $route = route('import-pdf.preview', $duplicate->id);

            return back()->with('info', __('imports.messages.pdf_duplicate_previous', [
                'status' => match ($duplicate->status) {
                    'extracted' => __('imports.status.extracted'),
                    'needs_review' => __('imports.status.needs_review'),
                    'committed' => __('imports.status.committed'),
                    'failed' => __('imports.status.failed'),
                    'processing' => __('imports.status.processing'),
                    default => $duplicate->status,
                },
                'url' => $route,
            ]));
        }

        $ext = $file->getClientOriginalExtension();
        $uuid = Str::uuid();
        $filename = "{$uuid}.{$ext}";
        $path = config('ocr.path', 'ocr');

        $storedPath = $file->storeAs($path, $filename, config('ocr.disk', 'local'));

        $import = DocumentImport::create([
            'user_id' => auth('web')->id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => $hash,
            'document_type' => $documentType,
            'template' => $documentType === 'borang_bantuan_maij' ? 'Borang Agihan Zakat' : null,
            'status' => 'processing',
            'page_count' => 1,
        ]);

        $fastPath = $this->ocr->processTextLayer($import);

        if (! $fastPath) {
            $fastPath = $this->ocr->processScanFastPath($import);
        }

        if (! $fastPath) {
            ProcessDocumentImport::dispatch($import->id);
        }

        AuditLogger::pdfImported(auth('web')->user(), $import, $request);

        // Axios upload flow returns JSON (scan_id + status_url) so the page can
        // poll progress; standard form uploads keep the redirect to the review.
        if ($request->wantsJson()) {
            return response()->json([
                'scan_id' => $import->id,
                'status_url' => route('import-pdf.status', $import->id),
                'review_url' => route('import-pdf.review', $import->id),
                'fast_path' => (bool) $fastPath,
            ]);
        }

        return redirect()
            ->route('import-pdf.preview', $import->id)
            ->with('success', $fastPath
                ? __('imports.messages.pdf_extracted')
                : __('imports.messages.pdf_processing_started'));
    }

    public function preview(string $id): View|RedirectResponse
    {
        $import = DocumentImport::findOrFail($id);

        if (! $import->type()->isBorang() && ! in_array($import->document_type, [null, '', 'generic'], true)) {
            return redirect()->route('ocr.show', $import->id);
        }

        if ($import->status === 'processing') {
            return view('import-pdf.preview', [
                'import' => $import,
                'extracted' => [],
                'processing' => true,
            ]);
        }

        if ($import->status === 'failed') {
            return view('import-pdf.preview', [
                'import' => $import,
                'extracted' => [],
                'processing' => false,
                'error' => $import->error_message,
            ]);
        }

        $extracted = $import->extracted_data ?? [];
        $householdMembers = $import->household_members_extracted ?? [];

        $householdLookup = null;
        $icValue = $extracted['ic']['value'] ?? null;
        if ($icValue) {
            $householdLookup = (new ImportHouseholdLookupService)->lookupByIC($icValue);
        }

        $duplicateWarnings = null;
        if (! empty($extracted)) {
            $duplicateWarnings = (new ImportDuplicateDetectionService)->analyze($extracted);
        }

        $validationResults = null;
        if (! empty($extracted) && $import->status !== 'processing') {
            $validationResults = (new ImportValidationService)->validate($import);
        }

        return view('import-pdf.preview', [
            'import' => $import,
            'extracted' => $extracted,
            'processing' => false,
            'confidence' => $import->overall_confidence ?? 0,
            'householdMembers' => $householdMembers,
            'householdLookup' => $householdLookup,
            'duplicateWarnings' => $duplicateWarnings,
            'validationResults' => $validationResults,
            'ocrResult' => $import->ocr_result ?? null,
        ]);
    }

    public function review(string $id): View|RedirectResponse
    {
        $import = DocumentImport::findOrFail($id);

        if (! $import->type()->isBorang() && ! in_array($import->document_type, [null, '', 'generic'], true)) {
            return redirect()->route('ocr.show', $import->id);
        }

        if ($import->status === 'processing') {
            return view('import-pdf.review', [
                'import' => $import,
                'extracted' => [],
                'processing' => true,
            ]);
        }

        if ($import->status === 'failed') {
            return view('import-pdf.review', [
                'import' => $import,
                'extracted' => [],
                'processing' => false,
                'error' => $import->error_message,
            ]);
        }

        $extracted = $import->extracted_data ?? [];

        return view('import-pdf.review', [
            'import' => $import,
            'extracted' => $extracted,
            'processing' => false,
            'confidence' => $import->overall_confidence ?? 0,
            'ocrResult' => $import->ocr_result ?? null,
        ]);
    }

    public function validate(Request $request, string $id): JsonResponse
    {
        $import = DocumentImport::findOrFail($id);

        if (! in_array($import->status, ['extracted', 'needs_review'])) {
            return response()->json(['error' => __('imports.messages.pdf_validation_not_ready')], 400);
        }

        $validationService = new ImportValidationService;
        $results = $validationService->validate($import);

        $duplicateService = new ImportDuplicateDetectionService;
        $duplicatedWarnings = $duplicateService->analyze($import->extracted_data ?? []);

        $import->update([
            'validation_results' => $results,
            'duplicate_warnings' => $duplicatedWarnings,
            'status' => $results['has_critical_errors'] ? 'needs_review' : 'extracted',
        ]);

        return response()->json([
            'validation' => $results,
            'duplicate_warnings' => $duplicatedWarnings,
            'status' => $import->status,
        ]);
    }

    public function householdLookup(Request $request): JsonResponse
    {
        $ic = $request->get('ic');
        if (! $ic || strlen(preg_replace('/\D/', '', $ic)) < 12) {
            return response()->json(['found' => false, 'error' => __('imports.messages.pdf_invalid_ic')], 400);
        }

        $ic = preg_replace('/\D/', '', $ic);
        $lookupService = new ImportHouseholdLookupService;
        $result = $lookupService->lookupByIC($ic);

        if (! $result) {
            return response()->json(['found' => false]);
        }

        return response()->json($result);
    }

    public function commit(Request $request, string $id): RedirectResponse
    {
        $import = DocumentImport::findOrFail($id);

        if (! $import->type()->isBorang() && ! in_array($import->document_type, [null, '', 'generic'], true)) {
            return redirect()->route('ocr.show', $import->id)
                ->with('info', __('document_ocr.review.extracted_description'));
        }

        if (! in_array($import->status, ['extracted', 'needs_review'])) {
            return back()->with('error', __('imports.messages.pdf_not_ready', ['status' => match ($import->status) {
                'extracted' => __('imports.status.extracted'),
                'needs_review' => __('imports.status.needs_review'),
                'committed' => __('imports.status.committed'),
                'failed' => __('imports.status.failed'),
                'processing' => __('imports.status.processing'),
                default => $import->status,
            }]));
        }

        $hasCriticalLowConfidence = $this->checkLowConfidenceFields($import);
        if ($hasCriticalLowConfidence && ! $request->has('force_import')) {
            return back()->with('error', __('imports.messages.pdf_low_confidence'));
        }

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'ic' => 'required|string|max:255',
            'ic_ketua_rumah' => 'nullable|string|max:255',
            'hubungan' => 'nullable|string|max:255',
            'status_perkahwinan' => 'nullable|string|max:255',
            'merokok' => 'nullable',
            'telefon' => 'nullable|string|max:255',
            'no_telefon_bimbit' => 'nullable|string|max:255',
            'no_telefon_rumah' => 'nullable|string|max:255',
            'kesihatan' => 'nullable|string',
            'alamat' => 'nullable|string',
            'daerah' => 'nullable|string|max:255',
            'bandar' => 'nullable|string|max:255',
            'poskod' => 'nullable|string|max:5',
            'dun' => 'nullable|string|max:255',
            'parlimen' => 'nullable|string|max:255',
            'pendapatan' => 'nullable|numeric|min:0',
            'bilangan_tanggungan' => 'nullable|integer|min:0',
            'sewa' => 'nullable|numeric|min:0',
            'utiliti' => 'nullable|numeric|min:0',
            'pengangkutan_input' => 'nullable|numeric|min:0',
            'perubatan_input' => 'nullable|numeric|min:0',
            'sebab_memohon' => 'nullable|string',
            'force_import' => 'nullable',
            'household_members' => 'nullable|array',
            'household_members.*.nama' => 'nullable|string|max:255',
            'household_members.*.ic' => 'nullable|string|max:255',
            'household_members.*.hubungan' => 'nullable|string|max:255',
            'household_members.*.umur' => 'nullable|integer|min:0|max:120',
            'household_members.*.pekerjaan' => 'nullable|string|max:255',
            'household_members.*.pendidikan' => 'nullable|string|max:255',
            'household_members.*.pendapatan_bulanan' => 'nullable|numeric|min:0',
            'household_members.*.status_kesihatan' => 'nullable|string|max:255',
        ]);

        $ic = preg_replace('/\D/', '', $data['ic']);
        $data['ic'] = $ic;

        $existingProfil = ProfilAsnaf::where('ic', $ic)->first();
        if ($existingProfil && ! $request->has('force_import')) {
            $profileLink = route('viewprofile', $existingProfil->ic);

            return back()->with(
                'error',
                __('imports.messages.pdf_existing_profile', ['ic' => $ic, 'name' => $existingProfil->nama, 'url' => $profileLink])
            )->withInput();
        }

        // Duplicate application detection (portal submissions / prior scans).
        $existingApplication = Application::where('ic_no', $ic)->latest()->first();
        if ($existingApplication && ! $request->has('force_import')) {
            return back()->with(
                'error',
                __('imports.messages.pdf_existing_application', [
                    'ic' => $ic,
                    'status' => match ($existingApplication->status) {
                        'draft' => __('imports.status.pending'),
                        'submitted' => __('imports.status.sent'),
                        'under_review' => __('common.status.dalam_semakan'),
                        'approved' => __('common.status.layak'),
                        'rejected' => __('common.status.tidak_layak'),
                        default => $existingApplication->status,
                    },
                ])
            )->withInput();
        }

        $ocr = $this->ocr;

        try {
            DB::beginTransaction();

            if ($existingProfil && $request->has('force_import')) {
                $profil = $existingProfil;
                $profil->fill(collect($data)->except(['ic', 'force_import'])->toArray());
                EligibilityService::apply($profil);
                $profil->save();
            } else {
                $profil = $ocr->commit($import, $data);
            }

            $householdMember = HouseholdMember::with('household')->where('ic', $ic)->first();
            if (! $householdMember) {
                $household = Household::create([
                    'alamat' => $data['alamat'] ?? $profil->alamat,
                    'daerah' => $data['daerah'] ?? $profil->daerah,
                    'mukim' => null,
                    'parlimen' => $data['parlimen'] ?? $profil->parlimen,
                    'dun' => $data['dun'] ?? $profil->dun,
                    'total_income' => $data['pendapatan'] ?? $profil->pendapatan ?? 0,
                    'total_expenses' => ($data['sewa'] ?? 0) + ($data['utiliti'] ?? 0) + ($data['pengangkutan_input'] ?? 0) + ($data['perubatan_input'] ?? 0),
                    'ai_score' => $profil->ai_score ?? 0,
                    'eligibility_status' => $profil->status_kelayakan ?? 'Dalam Semakan',
                    'status' => 'active',
                ]);

                $age = $this->calculateAgeFromIC($ic);
                $member = HouseholdMember::create([
                    'household_id' => $household->id,
                    'profil_asnaf_id' => $profil->id,
                    'nama' => $profil->nama,
                    'ic' => $profil->ic,
                    'hubungan' => $data['hubungan'] ?? 'Ketua Isi Rumah',
                    'umur' => $age,
                    'pekerjaan' => null,
                    'pendidikan' => null,
                    'pendapatan_bulanan' => $profil->pendapatan ?? 0,
                    'status_kesihatan' => $data['kesihatan'] ?? $profil->kesihatan,
                    'applicant_status' => 'active',
                    'assistance_status' => null,
                ]);

                $householdMembers = $request->input('household_members', []);
                if (empty($householdMembers)) {
                    $householdMembers = $import->household_members_extracted ?? [];
                }
                if (empty($householdMembers) && ! empty($import->ocr_result['dependents'] ?? [])) {
                    $householdMembers = array_values(array_map(fn ($d) => [
                        'nama' => $d['nama_penuh'] ?? null,
                        'ic' => $d['no_ic'] ?? null,
                        'hubungan' => $d['hubungan'] ?? null,
                        'pekerjaan' => $d['sekolah_pekerjaan'] ?? null,
                        'pendapatan_bulanan' => $d['pendapatan'] ?? 0,
                        'status_kesihatan' => null,
                    ], array_filter($import->ocr_result['dependents'], fn ($d) => ! empty($d['nama_penuh'] ?? null) || ! empty($d['no_ic'] ?? null))));
                }
                foreach ($householdMembers as $hmData) {
                    $hmIc = preg_replace('/\D/', '', $hmData['ic'] ?? '');
                    if (empty($hmData['nama'] ?? null) || $hmIc === $ic || strlen($hmIc) !== 12) {
                        continue;
                    }

                    HouseholdMember::create([
                        'household_id' => $household->id,
                        'nama' => $hmData['nama'] ?? null,
                        'ic' => $hmIc ?: null,
                        'hubungan' => $hmData['hubungan'] ?? null,
                        'umur' => $hmData['umur'] ?? 0,
                        'pekerjaan' => $hmData['pekerjaan'] ?? null,
                        'pendidikan' => $hmData['pendidikan'] ?? null,
                        'pendapatan_bulanan' => $hmData['pendapatan_bulanan'] ?? 0,
                        'status_kesihatan' => $hmData['status_kesihatan'] ?? null,
                        'applicant_status' => 'inactive',
                        'assistance_status' => null,
                    ]);
                }

                $import->update(['household_id' => $household->id]);
            } else {
                $import->update(['household_id' => $householdMember->household_id]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', __('imports.messages.pdf_save_failed', ['error' => $e->getMessage()]))->withInput();
        }

        AuditLogger::pdfCommitted(auth('web')->user(), $import, $profil, $request);

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'pdf_imported',
            __('imports.messages.pdf_notification_title'),
            __('imports.messages.pdf_notification_body', [
                'name' => $profil->nama,
                'time' => now()->subMinutes(rand(0, 2))->diffForHumans(),
            ]),
            'fa-solid fa-file-pdf',
            'success',
            route('viewprofile', $profil->ic)
        );

        try {
            EligibilityService::apply($profil);
            $profil->save();
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('viewprofile', $profil->ic)
            ->with('success', __('imports.messages.pdf_committed', ['status' => $profil->status_kelayakan, 'score' => $profil->ai_score]));
    }

    public function reprocess(Request $request, string $id): RedirectResponse
    {
        set_time_limit((int) config('ocr.limits.sync_request_timeout_seconds', 60));
        ini_set('memory_limit', '512M');

        $import = DocumentImport::findOrFail($id);

        if ($import->status === 'committed') {
            return back()->with('info', __('imports.messages.pdf_already_committed'));
        }

        app(\App\Services\Document\DocumentPreviewService::class)->clear($import);

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'ocr_started_at' => now(),
            'ocr_completed_at' => null,
            'progress' => 0,
        ]);

        ProcessDocumentImport::dispatchSync($import->id);

        return redirect()
            ->route('import-pdf.preview', $import->id)
            ->with('success', __('imports.messages.pdf_reprocessed'));
    }

    public function history(Request $request): View
    {
        $query = DocumentImport::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                    ->orWhere('profil_asnaf_ic', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $items = $query->with('user')->latest()->paginate(25);

        $totals = [
            'all' => DocumentImport::count(),
            'extracted' => DocumentImport::whereIn('status', ['extracted', 'needs_review'])->count(),
            'committed' => DocumentImport::where('status', 'committed')->count(),
            'failed' => DocumentImport::where('status', 'failed')->count(),
            'processing' => DocumentImport::where('status', 'processing')->count(),
        ];

        return view('import-pdf.history', compact('items', 'totals'));
    }

    public function show(string $id): View
    {
        $import = DocumentImport::with('user')->findOrFail($id);
        $extracted = $import->extracted_data ?? [];

        return view('import-pdf.show', compact('import', 'extracted'));
    }

    public function file(string $id)
    {
        $import = DocumentImport::findOrFail($id);

        $disk = config('ocr.disk', 'local');

        if (! Storage::disk($disk)->exists($import->stored_path)) {
            abort(404, __('imports.messages.pdf_file_missing'));
        }

        return response()->file(
            Storage::disk($disk)->path($import->stored_path),
            [
                'Content-Type' => $import->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function destroy(string $id): RedirectResponse
    {
        $import = DocumentImport::findOrFail($id);

        $admin = auth('web')->user();
        $isSuperAdmin = $admin && in_array($admin->email, (array) config('app.super_admin_emails'), true);

        if ((int) $import->user_id !== (int) $admin?->id && ! $isSuperAdmin) {
            abort(403);
        }

        $disk = config('ocr.disk', 'local');

        app(\App\Services\Document\DocumentPreviewService::class)->clear($import);

        if (Storage::disk($disk)->exists($import->stored_path)) {
            Storage::disk($disk)->delete($import->stored_path);
        }

        $import->delete();

        return redirect()
            ->route('import-pdf.index')
            ->with('success', __('imports.messages.pdf_deleted'));
    }

    private function checkLowConfidenceFields(DocumentImport $import): bool
    {
        $extracted = $import->extracted_data ?? [];

        $criticalFields = ['nama', 'ic'];
        foreach ($criticalFields as $field) {
            $conf = $extracted[$field]['confidence'] ?? 0;
            if ($conf < 30) {
                return true;
            }
        }

        return false;
    }

    private function calculateAgeFromIC(string $ic): int
    {
        if (strlen($ic) < 6) {
            return 0;
        }

        $yy = (int) substr($ic, 0, 2);
        $mm = (int) substr($ic, 2, 2);
        $dd = (int) substr($ic, 4, 2);
        $year = $yy > 30 ? 1900 + $yy : 2000 + $yy;

        try {
            $birthDate = Carbon::createFromDate($year, $mm, $dd);

            return $birthDate->age;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
