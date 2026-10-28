<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationReceivedMail;
use App\Mail\ApplicationSubmittedMail;
use App\Models\Admin;
use App\Models\Application;
use App\Models\AssistanceHistory;
use App\Models\ProfilAsnaf;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index()
    {
        $user = auth('portal')->user();
        $applications = $user->applications()->latest()->paginate(10);

        $stats = [
            'total' => $user->applications()->count(),
            'draft' => $user->applications()->where('status', 'draft')->count(),
            'submitted' => $user->applications()->whereIn('status', ['submitted', 'under_review'])->count(),
            'approved' => $user->applications()->where('status', 'approved')->count(),
        ];

        $latestDraft = $user->applications()->where('status', 'draft')->latest()->first();

        return view('portal.applications.index', compact('user', 'applications', 'stats', 'latestDraft'));
    }

    public function create()
    {
        $user = auth('portal')->user();
        $states = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Labuan', 'WP Putrajaya'];
        $assistanceTypes = AssistanceHistory::jenisBantuanOptions();

        return view('portal.applications.create', compact('user', 'states', 'assistanceTypes'));
    }

    public function store(Request $request)
    {
        $user = auth('portal')->user();

        $maxSize = (int) config('ocr.max_file_size_kb', 10240);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'ic_no' => ['required', 'string', 'max:12'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'postcode' => ['nullable', 'string', 'max:5'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'household_count' => ['required', 'integer', 'min:1', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['required', 'numeric', 'min:0'],
            'spouse_income' => ['nullable', 'numeric', 'min:0'],
            'income_source' => ['nullable', 'in:employment,business,pension,none,other'],
            'assistance_type' => ['nullable', 'string', 'max:100'],
            'assistance_frequency' => ['nullable', 'in:monthly,one_off'],
            'ic_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.$maxSize],
            'salary_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.$maxSize],
        ]);

        $validated['user_id'] = $user->id;
        $validated['status'] = 'draft';
        $validated['spouse_income'] = $validated['spouse_income'] ?? 0;
        $validated['total_household_income'] = $validated['monthly_income'] + $validated['spouse_income'];
        $validated['ic_document'] = $this->storeUpload($request, 'ic_document');
        $validated['salary_document'] = $this->storeUpload($request, 'salary_document');

        $application = Application::create($validated);

        return redirect()->route('user.applications.show', $application->id)
            ->with('success', __('applications.messages.created'));
    }

    private function storeUpload(Request $request, string $key): ?string
    {
        if (! $request->hasFile($key)) {
            return null;
        }

        return $request->file($key)->store('applications', 'local');
    }

    public function show($id)
    {
        $user = auth('portal')->user();
        $application = $user->applications()->findOrFail($id);

        return view('portal.applications.show', compact('user', 'application'));
    }

    public function status()
    {
        $user = auth('portal')->user();
        $applications = $user->applications()->latest()->get();

        return view('portal.applications.status', compact('user', 'applications'));
    }

    public function submit($id)
    {
        $user = auth('portal')->user();
        $application = $user->applications()->findOrFail($id);

        if ($application->status !== 'draft') {
            return back()->with('error', __('applications.messages.only_draft_submit'));
        }

        $application->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        ProfilAsnaf::firstOrCreate(
            ['ic' => $application->ic_no],
            [
                'nama' => $application->full_name,
                'ic_ketua_rumah' => null,
                'hubungan' => null,
                'telefon' => $application->phone,
                'alamat' => $application->address,
                'pendapatan' => (float) $application->monthly_income,
                'bilangan_tanggungan' => (int) $application->household_count,
                'sewa' => 0,
                'utiliti' => 0,
                'n_isteri_suami' => 0,
                'n_ibu_bapa' => 0,
                'n_dewasa_tidak_bekerja' => 0,
                'n_dewasa_bekerja_tanggung' => 0,
                'n_ipt' => 0,
                'n_remaja' => 0,
                'n_kanak_rendah' => 0,
                'n_kanak_tadika' => 0,
                'perumahan' => 'sendiri',
                'pengangkutan_input' => 0,
                'perubatan_input' => 0,
                'status_kelayakan' => 'Dalam Semakan',
                'approval_status' => 'pending',
            ]
        );

        AuditLogger::applicationSubmitted($user, $application, request());

        if (! $user->ic_no) {
            $user->ic_no = $application->ic_no;
            $user->save();
        }

        foreach (Admin::all() as $admin) {
            try {
                Mail::to($admin->email)->send(new ApplicationSubmittedMail($user, $application, $admin->language));
            } catch (\Throwable $e) {
                Log::warning('Gagal menghantar emel permohonan baharu kepada pentadbir.', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            Mail::to($user->email)->send(new ApplicationReceivedMail($application));
        } catch (\Throwable $e) {
            Log::warning('Gagal menghantar emel pengesahan permohonan kepada pemohon.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        NotificationService::notifyUsers(
            [$user->id],
            'application_submitted_user',
            __('applications.notifications.submitted_user_title'),
            __('applications.notifications.submitted_user_body'),
            'fa-solid fa-file-import',
            'success',
            route('user.applications.show', $application->id),
        );

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'application_submitted',
            __('applications.notifications.submitted_admin_title'),
            "{$application->full_name} ({$application->ic_no}) · ".now()->diffForHumans(),
            'fa-solid fa-file-import',
            'success',
            route('viewprofile', $application->ic_no, false),
        );

        return redirect()->route('user.applications.show', $application->id)
            ->with('success', __('applications.messages.submitted'));
    }

    public function downloadLetter($id)
    {
        $user = auth('portal')->user();
        $application = $user->applications()->findOrFail($id);

        $profil = ProfilAsnaf::where('ic', $application->ic_no)->first();

        if (! $profil || ! $profil->letter_path || ! Storage::disk('local')->exists($profil->letter_path)) {
            return back()->with('error', __('applications.messages.letter_missing'));
        }

        $filename = $profil->letter_type === 'approved'
            ? 'Surat-Kelulusan-MAIJ-'.$profil->ic.'.pdf'
            : 'Notis-Penolakan-MAIJ-'.$profil->ic.'.pdf';

        return Storage::disk('local')->download($profil->letter_path, $filename);
    }

    public function receipt($id)
    {
        $user = auth('portal')->user();
        $application = $user->applications()->findOrFail($id);

        if (! $application->submitted_at) {
            return back()->with('error', __('applications.messages.receipt_requires_submitted'));
        }

        $data = [
            'application' => $application,
            'user' => $user,
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('pdf.application-receipt', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('Resit-Permohonan-'.$application->application_no.'.pdf');
    }
}
