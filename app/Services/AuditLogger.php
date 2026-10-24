<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use App\Services\Concerns\ParsesUserAgent;
use Illuminate\Http\Request;

class AuditLogger
{
    use ParsesUserAgent;

    public static function authLogin($user, Request $request, string $status = 'success'): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($user);

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_role' => $role,
            'user_email' => $user?->email,
            'action' => $role.'.login',
            'category' => 'auth',
            'description' => $status === 'success'
                ? 'Log masuk berjaya'
                : 'Percubaan log masuk gagal',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => ['status' => $status],
        ]);
    }

    public static function authLogout($user, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($user);

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_role' => $role,
            'user_email' => $user?->email,
            'action' => $role.'.logout',
            'category' => 'auth',
            'description' => 'Log keluar',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
        ]);
    }

    public static function passwordChanged($user, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($user);

        if (method_exists($user, 'forceFill') && $user instanceof Admin) {
            $user->forceFill(['must_change_password' => false])->save();
        }

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_role' => $role,
            'user_email' => $user?->email,
            'action' => $role.'.password_change',
            'category' => 'security',
            'description' => 'Kata laluan akaun ditukar',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
        ]);
    }

    public static function passwordReset($user, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($user);

        if ($user instanceof Admin) {
            $user->forceFill(['must_change_password' => false])->save();
        }

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_role' => $role,
            'user_email' => $user?->email,
            'action' => $role.'.password_reset',
            'category' => 'security',
            'description' => 'Kata laluan ditetapkan semula melalui emel',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
        ]);
    }

    public static function profileUpdated($user, Request $request, array $changes = []): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($user);

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_role' => $role,
            'user_email' => $user?->email,
            'action' => $role.'.profile_updated',
            'category' => 'security',
            'description' => 'Profil dikemaskini'.(empty($changes) ? '' : ' ('.implode(', ', array_keys($changes)).')'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => $changes ?: null,
        ]);
    }

    public static function applicationCreated($actor, ProfilAsnaf $profil, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($actor);

        return AuditLog::create([
            'user_id' => $actor?->id,
            'user_role' => $role,
            'user_email' => $actor?->email,
            'action' => $role === 'admin' ? 'application_created' : 'application_created_portal',
            'category' => 'application',
            'description' => "Profil asnaf baharu dicipta: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'status' => $profil->status_kelayakan,
                'ai_score' => $profil->ai_score,
            ],
        ]);
    }

    public static function applicationUpdated($actor, ProfilAsnaf $profil, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);
        [$role] = self::roleFor($actor);

        return AuditLog::create([
            'user_id' => $actor?->id,
            'user_role' => $role,
            'user_email' => $actor?->email,
            'action' => $role === 'admin' ? 'application_updated' : 'application_updated_portal',
            'category' => 'application',
            'description' => "Profil asnaf dikemaskini: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
            ],
        ]);
    }

    public static function applicationApproved(Admin $admin, ProfilAsnaf $profil, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'application_approved',
            'category' => 'application',
            'description' => "Permohonan diluluskan: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'approved_by' => $admin->id,
                'approved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function applicationRejected(Admin $admin, ProfilAsnaf $profil, Request $request, ?string $remarks = null): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'application_rejected',
            'category' => 'application',
            'description' => "Permohonan ditolak: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'rejected_by' => $admin->id,
                'rejected_at' => now()->toIso8601String(),
                'remarks' => $remarks,
            ],
        ]);
    }

    public static function applicationSubmitted(PortalUser $portalUser, $application, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $portalUser->id,
            'user_role' => 'portal',
            'user_email' => $portalUser->email,
            'action' => 'application_submitted_portal',
            'category' => 'application',
            'description' => "Permohonan baharu dihantar oleh pemohon: {$portalUser->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'application_id' => $application->id ?? null,
                'full_name' => $application->full_name ?? null,
                'ic_no' => $application->ic_no ?? null,
            ],
        ]);
    }

    public static function additionalDocumentsRequested(Admin $admin, ProfilAsnaf $profil, array $documents, Request $request, ?string $note = null): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'documents_requested',
            'category' => 'application',
            'description' => "Dokumen tambahan diminta: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'documents' => $documents,
                'note' => $note,
                'requested_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function csvImported(Admin $admin, array $result, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'csv_imported',
            'category' => 'data',
            'description' => "Import CSV: {$result['success_count']} berjaya, {$result['failed_count']} gagal ({$result['total_rows']} jumlah)",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'file_name' => $result['file_name'] ?? null,
                'total_rows' => $result['total_rows'] ?? 0,
                'success_count' => $result['success_count'] ?? 0,
                'failed_count' => $result['failed_count'] ?? 0,
                'errors_count' => $result['errors_count'] ?? 0,
                'imported_by' => $admin->id,
                'imported_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function pdfImported(Admin $admin, $import, Request $request, ?float $processingSeconds = null): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        $extractedFields = is_array($import->extracted_data ?? null)
            ? array_keys($import->extracted_data)
            : [];

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'pdf_imported',
            'category' => 'data',
            'description' => "Import PDF/OCR: {$import->original_filename}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'file_name' => $import->original_filename,
                'import_id' => $import->id,
                'status' => $import->status,
                'overall_confidence' => $import->overall_confidence ?? null,
                'extracted_fields' => $extractedFields,
                'processing_time' => $processingSeconds,
                'imported_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function pdfCommitted(Admin $admin, $import, ProfilAsnaf $profil, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        $extractedFields = is_array($import->extracted_data ?? null)
            ? array_keys($import->extracted_data)
            : [];

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'pdf_committed',
            'category' => 'data',
            'description' => "Data PDF/OCR disahkan: {$import->original_filename} → {$profil->nama}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'file_name' => $import->original_filename,
                'import_id' => $import->id,
                'status' => 'committed',
                'overall_confidence' => $import->overall_confidence ?? null,
                'extracted_fields' => $extractedFields,
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'committed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function borangScanUploaded(PortalUser $user, $import, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $user->id,
            'user_role' => 'portal',
            'user_email' => $user->email,
            'action' => 'borang_scan_uploaded',
            'category' => 'data',
            'description' => "Imbas Borang Bantuan MAIJ dimuat naik: {$import->original_filename}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'file_name' => $import->original_filename,
                'import_id' => $import->id,
                'document_type' => $import->document_type,
            ],
        ]);
    }

    public static function borangScanCommitted(PortalUser $user, $import, Application $application, ProfilAsnaf $profil, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $user->id,
            'user_role' => 'portal',
            'user_email' => $user->email,
            'action' => 'borang_scan_committed',
            'category' => 'data',
            'description' => "Borang Bantuan MAIJ disimpan & dihantar: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'file_name' => $import->original_filename,
                'import_id' => $import->id,
                'application_id' => $application->id,
                'application_no' => $application->application_no,
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'ai_score' => $profil->ai_score,
                'status_kelayakan' => $profil->status_kelayakan,
                'overall_confidence' => $import->overall_confidence ?? null,
                'committed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function documentUploaded(PortalUser $user, $import, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $user->id,
            'user_role' => 'portal',
            'user_email' => $user->email,
            'action' => 'document_uploaded',
            'category' => 'data',
            'description' => "Dokumen dimuat naik: {$import->original_filename}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'file_name' => $import->original_filename,
                'import_id' => $import->id,
                'document_type' => $import->document_type,
            ],
        ]);
    }

    public static function aiAssessmentRun(Admin $admin, ProfilAsnaf $profil, Request $request, string $version = '1.0'): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'ai_assessment',
            'category' => 'system',
            'description' => "Penilaian AI dijalankan: {$profil->nama} ({$profil->ic})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => [
                'applicant_ic' => $profil->ic,
                'applicant_name' => $profil->nama,
                'ai_score' => $profil->ai_score,
                'status_kelayakan' => $profil->status_kelayakan,
                'had_kifayah' => $profil->had_kifayah,
                'monthly_income' => $profil->pendapatan,
                'assessment_version' => $version,
                'assessment_timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public static function moneyTransactionCreated(Admin $admin, $history, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'money_transaction_created',
            'category' => 'money',
            'description' => "Transaksi bantuan direkod: {$history->jenis_bantuan} RM {$history->amount}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => self::moneyMetadata($history, ['recorded_by' => $admin->id]),
        ]);
    }

    public static function moneyTransactionUpdated(Admin $admin, $history, array $changes, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'money_transaction_updated',
            'category' => 'money',
            'description' => "Transaksi bantuan dikemaskini: {$history->jenis_bantuan} RM {$history->amount}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => array_merge(self::moneyMetadata($history), [
                'recorded_by' => $admin->id,
                'changes' => $changes,
            ]),
        ]);
    }

    public static function moneyTransactionDeleted(Admin $admin, $history, Request $request): AuditLog
    {
        $agent = (new self)->parseAgent($request);

        return AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_email' => $admin->email,
            'action' => 'money_transaction_deleted',
            'category' => 'money',
            'description' => "Transaksi bantuan dipadam: {$history->jenis_bantuan} RM {$history->amount}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'metadata' => self::moneyMetadata($history, ['deleted_by' => $admin->id]),
        ]);
    }

    private static function moneyMetadata($history, array $extra = []): array
    {
        return array_merge([
            'transaction_id' => $history->id,
            'household_id' => $history->household_id,
            'household_member_id' => $history->household_member_id,
            'jenis_bantuan' => $history->jenis_bantuan,
            'amount' => (string) $history->amount,
            'frequency' => $history->frequency,
            'status' => $history->status,
            'approved_date' => $history->approved_date?->toDateString(),
            'expiry_date' => $history->expiry_date?->toDateString(),
            'approval_officer' => $history->approval_officer,
            'reference_number' => $history->reference_number,
            'recorded_at' => now()->toIso8601String(),
        ], $extra);
    }

    private static function roleFor($user): array
    {
        if ($user instanceof PortalUser) {
            return ['portal'];
        }
        if ($user instanceof Admin) {
            return ['admin'];
        }

        return ['system'];
    }
}
