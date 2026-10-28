<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use App\Services\ApprovalService;
use App\Services\AuditLogger;
use App\Services\HadAlKifayahService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ViewProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = ProfilAsnaf::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('ic', 'like', "%{$search}%");
            });
        }

        $allowed = ['nama', 'ic', 'pendapatan', 'had_kifayah', 'status_kelayakan', 'created_at'];
        $sort = in_array($request->query('sort'), $allowed) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $items = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();

        $statusCounts = [
            'layak' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_LAYAK)->count(),
            'tidak_layak' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_TIDAK_LAYAK)->count(),
            'dalam_semakan' => ProfilAsnaf::where('status_kelayakan', 'Dalam Semakan')->count(),
        ];

        return view('viewprofile.index', [
            'items' => $items,
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(string $ic)
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();

        $pendingPortalApp = Application::where('ic_no', $ic)
            ->where('status', 'submitted')
            ->latest('id')
            ->first();

        if ($pendingPortalApp) {
            $pendingPortalApp->update(['status' => 'under_review']);

            $owner = PortalUser::find($pendingPortalApp->user_id);
            if ($owner) {
                NotificationService::notifyUsers(
                    [$owner->id],
                    'application_under_review_user',
                    __('asnaf.notifications.under_review_title'),
                    __('asnaf.notifications.under_review_body', ['no' => $pendingPortalApp->application_no]),
                    'fa-solid fa-magnifying-glass-chart',
                    'info',
                    route('user.applications.show', $pendingPortalApp->id),
                );
            }
        }

        $confidence = $profil->confidenceScore();

        $recommendation = match ($profil->status_kelayakan) {
            HadAlKifayahService::STATUS_LAYAK => __('asnaf.recommendation.eligible'),
            HadAlKifayahService::STATUS_TIDAK_LAYAK => __('asnaf.recommendation.not_eligible'),
            default => __('asnaf.recommendation.under_review'),
        };

        return view('viewprofile', [
            'profil' => $profil,
            'aiScore' => (float) ($profil->ai_score ?? 0),
            'confidence' => $confidence,
            'recommendation' => $recommendation,
        ]);
    }

    public function approve(string $ic, ApprovalService $service)
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();

        $result = $service->approve($profil, auth()->id());

        AuditLogger::applicationApproved(auth('web')->user(), $profil, request());

        $msg = $result['email_sent']
            ? __('asnaf.flash.approved_email', ['name' => $profil->nama])
            : __('asnaf.flash.approved_download', ['name' => $profil->nama]);

        return redirect()
            ->route('viewprofile', $profil->ic)
            ->with('success', $msg);
    }

    public function reject(string $ic, Request $request, ApprovalService $service)
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();

        $result = $service->reject($profil, $request->input('approval_remarks'), auth()->id());

        AuditLogger::applicationRejected(auth('web')->user(), $profil, $request, $request->input('approval_remarks'));

        $msg = $result['email_sent']
            ? __('asnaf.flash.rejected_email', ['name' => $profil->nama])
            : __('asnaf.flash.rejected_download', ['name' => $profil->nama]);

        return redirect()
            ->route('viewprofile', $profil->ic)
            ->with('info', $msg);
    }

    public function downloadLetter(string $ic)
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();

        if (! $profil->letter_path || ! Storage::disk('local')->exists($profil->letter_path)) {
            return redirect()
                ->route('viewprofile', $profil->ic)
                ->with('error', __('asnaf.flash.letter_missing'));
        }

        $filename = $profil->letter_type === 'approved'
            ? 'Surat-Kelulusan-MAIJ-'.$profil->ic.'.pdf'
            : 'Notis-Penolakan-MAIJ-'.$profil->ic.'.pdf';

        return Storage::disk('local')->download($profil->letter_path, $filename);
    }

    public function destroy(string $ic)
    {
        $profil = ProfilAsnaf::where('ic', $ic)->firstOrFail();
        $profil->delete();

        return redirect()
            ->route('viewprofile.index')
            ->with('success', __('asnaf.flash.deleted', ['name' => $profil->nama]));
    }
}
