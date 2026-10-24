<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentImport;
use App\Mail\ApplicationReceivedMail;
use App\Mail\ApplicationSubmittedMail;
use App\Models\Admin;
use App\Models\Application;
use App\Models\DocumentImport;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\ProfilAsnaf;
use App\Services\Ai\AiService;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\NotificationService;
use App\Services\Ocr\OcrService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BantuanScanController extends Controller
{
    public function index(): View
    {
        $user = auth('portal')->user();
        $recent = DocumentImport::where('portal_user_id', $user->id)
            ->where('document_type', OcrService::DOC_TYPE_BORANG_BANTUAN)
            ->latest()
            ->limit(5)
            ->get();

        return view('portal.applications.scan', [
            'user' => $user,
            'recent' => $recent,
        ]);
    }

    public function upload(Request $request): RedirectResponse
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
        ], [
            'file.required' => 'Sila pilih fail Borang Bantuan MAIJ.',
            'file.mimes' => 'Format fail tidak disokong. Sila muat naik PDF, JPG, atau PNG.',
            'file.max' => 'Saiz fail melebihi had maksimum '.($maxSize / 1024).'MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth('portal')->user();
        $file = $request->file('file');
        $hash = sha1_file($file->getRealPath());

        $duplicate = DocumentImport::where('file_hash', $hash)
            ->where('portal_user_id', $user->id)
            ->first();

        if ($duplicate) {
            $route = route('user.applications.scan.preview', $duplicate->id);

            return back()->with('info', "Fail ini pernah dimuat naik sebelum ini. <a href=\"{$route}\" class=\"underline\">Lihat keputusan</a>.");
        }

        $ext = $file->getClientOriginalExtension();
        $uuid = Str::uuid();
        $filename = "{$uuid}.{$ext}";
        $path = config('ocr.path', 'ocr');
        $storedPath = $file->storeAs($path, $filename, 'local');

        $import = DocumentImport::create([
            'portal_user_id' => $user->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => $hash,
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'template' => 'Borang Agihan Zakat',
            'status' => 'processing',
            'page_count' => 1,
        ]);

        // Always process asynchronously. Running OCR synchronously inside the
        // request can hang the single-threaded dev server and block every other
        // request, so extraction happens in the queue worker instead.
        ProcessDocumentImport::dispatch($import->id);

        AuditLogger::borangScanUploaded($user, $import, $request);

        return redirect()
            ->route('user.applications.scan.preview', $import->id)
            ->with('success', 'Borang sedang diimbas. AI sedang mengekstrak maklumat...');
    }

    public function preview(string $id): View
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)
            ->findOrFail($id);

        if ($import->document_type !== OcrService::DOC_TYPE_BORANG_BANTUAN) {
            abort(404);
        }

        if ($import->status === 'processing') {
            return view('portal.applications.scan-preview', [
                'import' => $import,
                'extracted' => [],
                'processing' => true,
            ]);
        }

        if ($import->status === 'failed') {
            return view('portal.applications.scan-preview', [
                'import' => $import,
                'extracted' => [],
                'processing' => false,
                'error' => $import->error_message,
            ]);
        }

        if (in_array($import->status, ['committed'])) {
            $application = Application::where('borang_scan_id', $import->id)->first();

            return view('portal.applications.scan-preview', [
                'import' => $import,
                'extracted' => $import->extracted_data ?? [],
                'processing' => false,
                'committed' => true,
                'application' => $application,
            ]);
        }

        return view('portal.applications.scan-preview', [
            'import' => $import,
            'extracted' => $import->extracted_data ?? [],
            'processing' => false,
            'confidence' => $import->overall_confidence ?? 0,
            'householdMembers' => $import->household_members_extracted ?? [],
            'borangData' => $import->borang_data ?? [],
        ]);
    }

    public function commit(Request $request, string $id): RedirectResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)
            ->findOrFail($id);

        if ($import->document_type !== OcrService::DOC_TYPE_BORANG_BANTUAN) {
            abort(404);
        }

        if (! in_array($import->status, ['extracted', 'needs_review'])) {
            return back()->with('error', 'Data belum bersedia untuk disimpan. Status: '.$import->status);
        }

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'ic_no' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'postcode' => 'nullable|string|max:5',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'household_count' => 'required|integer|min:1|max:20',
            'monthly_income' => 'required|numeric|min:0',
            'spouse_income' => 'nullable|numeric|min:0',
            'occupation' => 'nullable|string|max:255',
            'confirm' => 'required|accepted',
        ]);

        $ic = preg_replace('/\D/', '', $data['ic_no']);
        $data['ic_no'] = $ic;

        if (strlen($ic) !== 12) {
            return back()->with('error', 'No. Kad Pengenalan mesti mengandungi 12 digit.');
        }

        $existingProfil = ProfilAsnaf::where('ic', $ic)->first();

        if ($existingProfil && $user->ic_no !== $ic) {
            return back()->with('error', 'No. IC ini telah digunakan oleh pemohon lain. Sila hubungi pentadbir.');
        }

        if ($user->ic_no && $user->ic_no !== $ic) {
            return back()->with('error', 'No. IC tidak sepadan dengan akaun anda. Sila semak semula.');
        }

        if ($existingProfil && $existingProfil->approval_status === 'approved') {
            return back()->with('error', 'No. IC ini sudah diluluskan. Hubungi pentadbir untuk kemas kini.');
        }

        try {
            DB::beginTransaction();

            $extracted = $import->extracted_data ?? [];

            $application = Application::create([
                'user_id' => $user->id,
                'borang_scan_id' => $import->id,
                'borang_data' => $this->buildBorangDataPayload($import, $extracted),
                'full_name' => $data['full_name'],
                'ic_no' => $ic,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
                'district' => $data['district'] ?? null,
                'state' => $data['state'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'household_count' => (int) $data['household_count'],
                'occupation' => $data['occupation'] ?? null,
                'monthly_income' => (float) $data['monthly_income'],
                'spouse_income' => (float) ($data['spouse_income'] ?? 0),
                'total_household_income' => (float) $data['monthly_income'] + (float) ($data['spouse_income'] ?? 0),
                'income_source' => 'other',
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $profilData = $this->buildProfilData($extracted, $data, $ic);

            if ($existingProfil) {
                $profil = $existingProfil;
                $profil->fill(collect($profilData)->except(['ic'])->toArray());
                $profil->borang_data = $application->borang_data;
            } else {
                $profil = ProfilAsnaf::create(array_merge($profilData, ['borang_data' => $application->borang_data]));
            }

            EligibilityService::apply($profil);
            $profil->save();

            if (! $user->ic_no) {
                $user->ic_no = $ic;
                $user->save();
            }

            $recommendation = $this->generateAiRecommendation($profil, $extracted);
            if ($recommendation !== null) {
                $profil->ai_recommendation = $recommendation;
                $profil->save();

                $enumMap = ['layak' => 'approve', 'tidak layak' => 'reject', 'dalam semakan' => 'review'];
                $statusKey = strtolower($profil->status_kelayakan ?? '');
                $application->ai_recommendation = $enumMap[$statusKey] ?? 'review';
            }

            $application->had_kifayah_result = $profil->had_kifayah;
            $application->ai_score = (int) round((float) ($profil->ai_score ?? 0));
            $application->save();

            $household = $this->ensureHousehold($profil, $data);

            $import->update([
                'status' => 'committed',
                'profil_asnaf_ic' => $profil->ic,
                'household_id' => $household?->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Ralat semasa menyimpan borang: '.$e->getMessage())->withInput();
        }

        AuditLogger::borangScanCommitted($user, $import, $application, $profil, $request);

        foreach (Admin::all() as $admin) {
            try {
                Mail::to($admin->email)->send(new ApplicationSubmittedMail($user, $application));
            } catch (\Throwable $e) {
                Log::warning('Gagal menghantar emel borang Bantuan MAIJ baharu.', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'application_submitted',
            'Permohonan Borang Bantuan MAIJ Dihantar',
            "{$profil->nama} ({$profil->ic}) · Skor AI: {$profil->ai_score}%",
            'fa-solid fa-file-contract',
            'success',
            route('viewprofile', $profil->ic)
        );

        try {
            Mail::to($user->email)->send(new ApplicationReceivedMail($application));
        } catch (\Throwable $e) {
            Log::warning('Gagal menghantar emel pengesahan borang MAIJ kepada pemohon.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        NotificationService::notifyUsers(
            [$user->id],
            'application_submitted_user',
            'Permohonan Dihantar',
            'Borang Bantuan MAIJ anda telah dihantar dan sedang menunggu semakan.',
            'fa-solid fa-file-contract',
            'success',
            route('user.applications.show', $application->id),
        );

        return redirect()
            ->route('user.applications.show', $application->id)
            ->with('success', "Borang Bantuan MAIJ berjaya dihantar. Skor AI: {$profil->ai_score}%. Status: {$profil->status_kelayakan}.");
    }

    public function reprocess(Request $request, string $id): RedirectResponse
    {
        set_time_limit((int) config('ocr.limits.sync_request_timeout_seconds', 60));
        ini_set('memory_limit', '512M');

        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)
            ->findOrFail($id);

        if ($import->status === 'committed') {
            return back()->with('info', 'Rekod ini telah dihantar — tidak perlu diproses semula.');
        }

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'ocr_started_at' => now(),
            'ocr_completed_at' => null,
        ]);

        ProcessDocumentImport::dispatchSync($import->id);

        return redirect()
            ->route('user.applications.scan.preview', $import->id)
            ->with('success', 'Pemprosesan semula selesai.');
    }

    public function status(string $id): JsonResponse
    {
        $user = auth('portal')->user();
        $import = DocumentImport::where('portal_user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'overall_confidence' => $import->overall_confidence,
            'error_message' => $import->error_message,
        ]);
    }

    private function buildBorangDataPayload(DocumentImport $import, array $extracted): array
    {
        $sections = $import->borang_data ?? [];

        $fields = [];
        $borangFieldKeys = [
            'jumlah_sara_hidup', 'no_akaun_bimb', 'bantuan_agensi_lain_ada', 'bantuan_agensi_lain_nyatakan', 'bantuan_agensi_lain_jumlah', 'tidak_pernah_terima_bantuan',
            'perubatan_jenis_rawatan', 'perubatan_jenis_penyakit', 'perubatan_tempat_rawatan', 'perubatan_kos_rawatan', 'perubatan_harga_alatan',
            'jenis_berdikari', 'berdikari_jenis_peralatan', 'berdikari_harga_peralatan', 'berdikari_jenis_perniagaan',
            'jenis_bantuan_perumahan', 'status_tanah', 'nama_dalam_geran', 'no_geran', 'perumahan_laporan_polis',
            'sebab_berhutang', 'jumlah_hutang', 'sebab_gagal_bayar_hutang', 'nama_pemberi_hutang', 'no_kp_pemberi_hutang',
            'tempat_tinggal_sendiri', 'tempat_tinggal_menumpang', 'tempat_tinggal_menyewe', 'lain_lain_nyatakan_tempat_tinggal',
            'harta_rumah', 'harta_tanah', 'harta_kereta', 'harta_motosikal',
            'pendapatan_diri', 'pendapatan_pasangan_ibubapa_penjaga', 'pendapatan_pencen_perkeso', 'pendapatan_sumbangan_anak', 'pendapatan_bantuan_agensi', 'pendapatan_lain_lain',
            'perbelanjaan_makan_minum', 'perbelanjaan_bil_api_air', 'perbelanjaan_persekolahan', 'perbelanjaan_sewa_ansuran',
            'bantuan_sara_hidup', 'bantuan_perubatan', 'bantuan_berdikari', 'bantuan_perumahan', 'menjelaskan_hutang',
            'jumlah_bantuan_diperlukan', 'sebab_memohon_bantuan',
        ];

        foreach ($borangFieldKeys as $key) {
            if (isset($extracted[$key])) {
                $fields[$key] = $extracted[$key];
            }
        }

        return [
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'template' => $import->template,
            'sections' => $sections,
            'fields' => $fields,
        ];
    }

    private function buildProfilData(array $extracted, array $overrides, string $ic): array
    {
        $value = fn (string $key) => $extracted[$key]['value'] ?? null;
        $float = fn (string $key) => (float) preg_replace('/[^\d.]/', '', (string) ($extracted[$key]['value'] ?? 0));

        return [
            'nama' => $overrides['full_name'] ?? $value('nama') ?? '',
            'ic' => $ic,
            'telefon' => $overrides['phone'] ?? $value('telefon') ?? $value('no_telefon_bimbit'),
            'no_telefon_bimbit' => $value('no_telefon_bimbit'),
            'no_telefon_rumah' => $value('no_telefon_rumah'),
            'alamat' => $overrides['address'] ?? $value('alamat'),
            'daerah' => $overrides['district'] ?? $value('daerah'),
            'bandar' => $value('bandar'),
            'poskod' => $overrides['postcode'] ?? $value('poskod'),
            'dun' => $value('dun'),
            'parlimen' => $value('parlimen'),
            'kesihatan' => $value('kesihatan'),
            'status_perkahwinan' => $value('status_perkahwinan'),
            'merokok' => $this->parseYesNo($value('merokok')),
            'pendapatan' => (float) $overrides['monthly_income'] ?: $float('pendapatan'),
            'bilangan_tanggungan' => (int) ($overrides['household_count'] ?: ($extracted['bilangan_tanggungan']['value'] ?? 0)),
            'sewa' => $float('sewa'),
            'utiliti' => $float('utiliti'),
            'pengangkutan_input' => $float('pengangkutan'),
            'perubatan_input' => $float('perubatan'),
            'sebab_memohon' => $value('sebab_memohon_bantuan'),
            'perumahan' => $value('perumahan') ?: 'sendiri',
            'status_kelayakan' => 'Dalam Semakan',
            'approval_status' => 'pending',
        ];
    }

    private function ensureHousehold(ProfilAsnaf $profil, array $data): ?Household
    {
        $existingMember = HouseholdMember::where('ic', $profil->ic)->first();
        if ($existingMember) {
            return $existingMember->household;
        }

        $age = $this->calculateAgeFromIC($profil->ic);
        $household = Household::create([
            'alamat' => $data['address'] ?? $profil->alamat,
            'daerah' => $data['district'] ?? $profil->daerah,
            'mukim' => null,
            'parlimen' => $profil->parlimen,
            'dun' => $profil->dun,
            'total_income' => (float) $data['monthly_income'],
            'total_expenses' => (float) ($profil->sewa ?? 0) + (float) ($profil->utiliti ?? 0) + (float) ($profil->pengangkutan_input ?? 0) + (float) ($profil->perubatan_input ?? 0),
            'ai_score' => (float) ($profil->ai_score ?? 0),
            'eligibility_status' => $profil->status_kelayakan,
            'status' => 'active',
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'profil_asnaf_id' => $profil->id,
            'nama' => $profil->nama,
            'ic' => $profil->ic,
            'hubungan' => 'Ketua Isi Rumah',
            'umur' => $age,
            'pekerjaan' => $data['occupation'] ?? null,
            'pendidikan' => null,
            'pendapatan_bulanan' => $profil->pendapatan,
            'status_kesihatan' => $profil->kesihatan,
            'applicant_status' => 'active',
            'assistance_status' => null,
        ]);

        return $household;
    }

    private function generateAiRecommendation(ProfilAsnaf $profil, array $extracted): ?string
    {
        try {
            $bantuan = [];
            foreach (['bantuan_sara_hidup', 'bantuan_perubatan', 'bantuan_berdikari', 'bantuan_perumahan', 'menjelaskan_hutang'] as $type) {
                if (($extracted[$type]['value'] ?? null) === 'Ya') {
                    $bantuan[] = $type;
                }
            }

            $prompt = sprintf(
                "Anda adalah pegawai zakat MAIJ. Beri satu cadangan ringkas (1-2 ayat) dalam Bahasa Melayu untuk pemohon berikut:\n\n".
                "Nama: %s\nIC: %s\nPendapatan: RM %.2f\nHad Kifayah: RM %.2f\nStatus: %s\nSkor AI: %s%%\nBantuan dipohon: %s\nSebab memohon: %s\n\n".
                "Mula dengan 'Profil ini LAYAK/TIDAK LAYAK/DALAM SEMAKAN. ' kemudian berikan satu cadangan tindakan.",
                $profil->nama,
                $profil->ic,
                (float) $profil->pendapatan,
                (float) $profil->had_kifayah,
                $profil->status_kelayakan,
                $profil->ai_score,
                implode(', ', $bantuan) ?: 'Tiada',
                $profil->sebab_memohon ?? 'Tidak dinyatakan'
            );

            $ai = new AiService;
            $response = $ai->chat($prompt);
            $reply = $response['reply'] ?? '';
            $reply = trim($reply);

            if ($reply === '') {
                return null;
            }

            if (mb_strlen($reply) > 1000) {
                $reply = mb_substr($reply, 0, 1000);
            }

            return $reply;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function parseYesNo(?string $value): bool
    {
        if (! $value) {
            return false;
        }
        $lower = mb_strtolower(trim($value));

        return in_array($lower, ['ya', 'yes', 'y', 'true', '1', '✓', '✔', '☑'], true);
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
