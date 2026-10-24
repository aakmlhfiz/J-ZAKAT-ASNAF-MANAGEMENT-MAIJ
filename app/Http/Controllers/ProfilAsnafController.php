<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Mail\AdditionalDocumentsRequestedMail;
use App\Models\Application;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilAsnafController extends Controller
{
    public function index(): View
    {
        return view('profilasnaf');
    }

    public function create(): View
    {
        return view('profilasnaf');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rulesForProfil());

        $data = $this->withDefaults($data);

        $profil = new ProfilAsnaf($data);
        EligibilityService::apply($profil);
        $profil->save();

        AuditLogger::applicationCreated(auth('web')->user(), $profil, $request);

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'application_new',
            'Pemohonan baharu didaftarkan',
            "{$profil->nama} · ".now()->subMinutes(rand(0, 5))->diffForHumans(),
            'fa-solid fa-user-plus',
            'success',
            route('viewprofile', $profil->ic)
        );

        return redirect()
            ->route('viewprofile', $profil->ic)
            ->with('success', "Permohonan berjaya disimpan. Keputusan: {$profil->status_kelayakan} (skor AI: {$profil->ai_score}%).");
    }

    public function edit(string $ic): View
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();

        return view('profilasnaf', ['profil' => $profil, 'mode' => 'edit']);
    }

    public function update(Request $request, string $ic): RedirectResponse
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();

        $data = $request->validate($this->rulesForProfil($profil->id));

        $data = $this->withDefaults($data);

        $profil->fill($data);
        EligibilityService::apply($profil);
        $profil->save();

        AuditLogger::applicationUpdated(auth('web')->user(), $profil, $request);

        return redirect()
            ->route('viewprofile', $profil->ic)
            ->with('success', "Profil dikemas kini. Keputusan: {$profil->status_kelayakan} (skor AI: {$profil->ai_score}%).");
    }

    public function destroy(string $ic): RedirectResponse
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();
        $profil->delete();

        return redirect()
            ->route('viewprofile.index')
            ->with('success', "Profil {$profil->nama} telah dipadam.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('viewprofile.index')->with('error', 'Tiada profil dipilih.');
        }

        $count = ProfilAsnaf::destroy($ids);

        return redirect()->route('viewprofile.index')->with('success', "{$count} profil telah berjaya dipadam.");
    }

    public function requestDocuments(Request $request, string $ic): RedirectResponse
    {
        $request->validate([
            'documents' => 'required|array|min:1',
            'documents.*' => ['required', 'string', Rule::enum(DocumentType::class)],
            'note' => 'nullable|string|max:1000',
        ]);

        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();
        $admin = auth('web')->user();

        $profil->update([
            'documents_requested_list' => $request->documents,
            'documents_request_note' => $request->note,
            'documents_requested_at' => now(),
            'documents_requested_by' => $admin->id,
        ]);

        $portalUser = PortalUser::where('ic_no', $ic)->first();

        if ($portalUser) {
            $portalApp = Application::where('ic_no', $ic)->whereIn('status', ['submitted', 'under_review'])->latest('id')->first();

            if ($portalApp) {
                $portalApp->update([
                    'status' => 'incomplete',
                    'admin_remarks' => $request->note,
                ]);
            }

            if ($portalUser->email) {
                try {
                    Mail::to($portalUser->email)->send(new AdditionalDocumentsRequestedMail(
                        $profil,
                        $request->documents,
                        $request->note,
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Gagal menghantar emel dokumen tambahan.', [
                        'portal_user_id' => $portalUser->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            NotificationService::notifyUsers(
                [$portalUser->id],
                'documents_requested_user',
                'Dokumen Tambahan Diminta',
                'Sila muat naik dokumen yang diminta untuk meneruskan permohonan anda.',
                'fa-solid fa-file-circle-plus',
                'warning',
                route('user.applications.index'),
            );
        }

        AuditLogger::additionalDocumentsRequested($admin, $profil, $request->documents, $request, $request->note);

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'documents_requested',
            'Dokumen Tambahan Diminta',
            "Untuk {$profil->nama} · ".count($request->documents).' dokumen',
            'fa-solid fa-file-circle-plus',
            'warning',
            route('viewprofile', $profil->ic)
        );

        return back()->with('success', 'Permohonan dokumen tambahan telah dihantar kepada pemohon.');
    }

    private function rulesForProfil(?int $excludeId = null): array
    {
        $icUnique = $excludeId
            ? "unique:profil_asnafs,ic,{$excludeId}"
            : 'unique:profil_asnafs,ic';

        return [
            'nama' => 'required|string|max:255',
            'ic' => "required|string|max:255|{$icUnique}",
            'ic_ketua_rumah' => 'nullable|string|max:255',
            'hubungan' => 'nullable|string|max:255',
            'telefon' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'pendapatan' => 'nullable|numeric|min:0',
            'bilangan_tanggungan' => 'nullable|integer|min:0',
            'sewa' => 'nullable|numeric|min:0',
            'utiliti' => 'nullable|numeric|min:0',
            'n_isteri_suami' => 'nullable|integer|min:0|max:20',
            'n_ibu_bapa' => 'nullable|integer|min:0|max:20',
            'n_dewasa_tidak_bekerja' => 'nullable|integer|min:0|max:20',
            'n_dewasa_bekerja_tanggung' => 'nullable|integer|min:0|max:20',
            'n_ipt' => 'nullable|integer|min:0|max:20',
            'n_remaja' => 'nullable|integer|min:0|max:20',
            'n_kanak_rendah' => 'nullable|integer|min:0|max:20',
            'n_kanak_tadika' => 'nullable|integer|min:0|max:20',
            'perumahan' => 'nullable|in:sendiri,sewa',
            'pengangkutan_input' => 'nullable|numeric|min:0',
            'perubatan_input' => 'nullable|numeric|min:0',
        ];
    }

    private function withDefaults(array $data): array
    {
        $defaults = [
            'n_isteri_suami' => 0, 'n_ibu_bapa' => 0,
            'n_dewasa_tidak_bekerja' => 0, 'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 0, 'n_remaja' => 0, 'n_kanak_rendah' => 0, 'n_kanak_tadika' => 0,
            'perumahan' => 'sendiri',
            'pengangkutan_input' => 0, 'perubatan_input' => 0,
        ];
        foreach ($defaults as $k => $v) {
            $data[$k] = $data[$k] ?? $v;
        }

        return $data;
    }
}
