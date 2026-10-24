<?php

namespace App\Services;

use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationRejectedMail;
use App\Models\Application;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ApprovalService
{
    public function approve(ProfilAsnaf $p, ?int $adminId = null): array
    {
        $adminId = $adminId ?? auth()->id();
        $now = now();
        $refNo = $this->generateRefNo($now);

        $data = [
            'profil' => $p,
            'admin' => auth()->user(),
            'type' => 'approved',
            'ref_no' => $refNo,
            'tarikh' => $now,
        ];

        $pdf = Pdf::loadView('pdf.letter-approved', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'MAIJ-APP-approved-'.$p->ic.'-'.$now->format('Ymd-His').'.pdf';
        $path = 'letters/'.$filename;

        Storage::disk('local')->put($path, $pdf->output());

        $p->update([
            'approval_status' => 'approved',
            'status_kelayakan' => HadAlKifayahService::STATUS_LAYAK,
            'approved_by' => $adminId,
            'approved_at' => $now,
            'approval_remarks' => null,
            'letter_path' => $path,
            'letter_type' => 'approved',
            'letter_generated_at' => $now,
        ]);

        $this->syncPortalApplication($p, 'approved', $now, null);

        $this->notifyPortalUser($p, 'approved');

        $emailSent = $this->sendEmail($p, 'approved');

        if ($emailSent) {
            $p->update(['letter_sent_at' => $now]);
        }

        return [
            'email_sent' => $emailSent,
            'letter_path' => $path,
        ];
    }

    public function reject(ProfilAsnaf $p, ?string $remarks = null, ?int $adminId = null): array
    {
        $adminId = $adminId ?? auth()->id();
        $now = now();
        $refNo = $this->generateRefNo($now);

        $data = [
            'profil' => $p,
            'admin' => auth()->user(),
            'type' => 'rejected',
            'remarks' => $remarks,
            'ref_no' => $refNo,
            'tarikh' => $now,
        ];

        $pdf = Pdf::loadView('pdf.letter-rejected', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'MAIJ-APP-rejected-'.$p->ic.'-'.$now->format('Ymd-His').'.pdf';
        $path = 'letters/'.$filename;

        Storage::disk('local')->put($path, $pdf->output());

        $p->update([
            'approval_status' => 'rejected',
            'status_kelayakan' => HadAlKifayahService::STATUS_TIDAK_LAYAK,
            'approved_by' => $adminId,
            'approved_at' => $now,
            'approval_remarks' => $remarks,
            'letter_path' => $path,
            'letter_type' => 'rejected',
            'letter_generated_at' => $now,
        ]);

        $this->syncPortalApplication($p, 'rejected', $now, $remarks);

        $this->notifyPortalUser($p, 'rejected', $remarks);

        $emailSent = $this->sendEmail($p, 'rejected');

        if ($emailSent) {
            $p->update(['letter_sent_at' => $now]);
        }

        return [
            'email_sent' => $emailSent,
            'letter_path' => $path,
        ];
    }

    private function syncPortalApplication(ProfilAsnaf $p, string $outcome, $timestamp, ?string $remarks): void
    {
        $portalApp = Application::where('ic_no', $p->ic)->latest('id')->first();

        if (! $portalApp) {
            return;
        }

        $update = [
            'status' => $outcome,
        ];

        if ($outcome === 'approved') {
            $update['approved_at'] = $timestamp;
            $update['rejected_at'] = null;
        } else {
            $update['rejected_at'] = $timestamp;
            $update['approved_at'] = null;
        }

        if ($remarks !== null) {
            $update['admin_remarks'] = $remarks;
        }

        $portalApp->update($update);
    }

    private function notifyPortalUser(ProfilAsnaf $p, string $type, ?string $remarks = null): void
    {
        $portalUser = PortalUser::where('ic_no', $p->ic)->first();

        if (! $portalUser) {
            return;
        }

        if ($type === 'approved') {
            NotificationService::notifyUsers(
                [$portalUser->id],
                'application_approved',
                'Permohonan Diluluskan',
                "Tahniah, permohonan {$p->nama} telah diluluskan.",
                'fa-solid fa-circle-check',
                'success',
                route('user.applications.index'),
            );
        } else {
            NotificationService::notifyUsers(
                [$portalUser->id],
                'application_rejected',
                'Permohonan Ditolak',
                'Permohonan anda telah ditolak'.($remarks ? ': '.$remarks : '.'),
                'fa-solid fa-circle-xmark',
                'danger',
                route('user.applications.index'),
            );
        }
    }

    private function sendEmail(ProfilAsnaf $p, string $type): bool
    {
        $portalUser = PortalUser::where('ic_no', $p->ic)->first();

        if (! $portalUser || ! $portalUser->email) {
            return false;
        }

        $mail = $type === 'approved'
            ? new ApplicationApprovedMail($p, $p->letter_path)
            : new ApplicationRejectedMail($p, $p->letter_path);

        Mail::to($portalUser->email)->send($mail);

        return true;
    }

    private function generateRefNo(\DateTimeInterface $date): string
    {
        $year = $date->format('Y');

        $count = DB::transaction(function () use ($year) {
            $seq = DB::table('approval_sequences')
                ->lockForUpdate()
                ->where('year', $year)
                ->first();

            if (! $seq) {
                DB::table('approval_sequences')->insert(['year' => $year, 'last_number' => 1]);

                return 1;
            }

            DB::table('approval_sequences')->where('year', $year)->increment('last_number');

            return $seq->last_number + 1;
        });

        return 'MAIJ/J-ZAKAT/'.$year.'/'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
