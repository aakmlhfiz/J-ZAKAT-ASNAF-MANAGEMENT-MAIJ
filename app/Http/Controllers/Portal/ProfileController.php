<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use App\Services\PortalTwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth('portal')->user();
        $userId = $user->id;

        $stats = [
            'login_count' => AuditLog::where('user_id', $userId)
                ->where('action', 'portal.login')->count(),
            'last_login' => AuditLog::where('user_id', $userId)
                ->where('action', 'portal.login')->latest('created_at')->first(),
            'account_created_date' => $user->created_at->format('d M Y'),
            'account_created_ago' => $user->created_at->diffForHumans(),
            'role_label' => $user->roleLabel(),
            'status_label' => $user->statusLabel(),
            'application_count' => $user->applications()->count(),
        ];

        $completionPercent = $user->profileCompletion();
        $securityScore = $user->securityScore();

        $recentActivities = AuditLog::where('user_id', $userId)
            ->where('user_role', 'portal')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $loginHistory = AuditLog::where('user_id', $userId)
            ->where('action', 'portal.login')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $currentSession = DB::connection('user')->table('sessions')
            ->where('user_id', $userId)
            ->where('id', session()->getId())
            ->first();

        $permissions = $this->permissionsForRole();

        return view('portal.profile', compact(
            'user', 'stats', 'completionPercent', 'securityScore',
            'recentActivities', 'loginHistory', 'currentSession', 'permissions',
        ));
    }

    public function update(Request $request)
    {
        $user = auth('portal')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:500'],
            'district' => ['nullable', 'string', 'max:100'],
            'mukim' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'state' => ['nullable', 'string', 'max:50'],
            'postcode' => ['nullable', 'string', 'max:5'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'notification_email' => ['nullable', 'boolean'],
            'notification_push' => ['nullable', 'boolean'],
            'notification_sms' => ['nullable', 'boolean'],
        ]);

        $changes = [];
        foreach ($validated as $key => $value) {
            if (($user->$key ?? null) !== $value) {
                $changes[$key] = ['old' => $user->$key ?? null, 'new' => $value];
            }
        }

        $user->update($validated);

        if (! empty($changes)) {
            AuditLogger::profileUpdated($user, $request, $changes);
        }

        return back()->with('success', 'Profil berjaya dikemaskini.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth('portal')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Kata laluan semasa tidak tepat.']);
        }

        $user->update(['password' => $validated['password']]);

        AuditLogger::passwordChanged($user, $request);

        return back()->with('success', 'Kata laluan berjaya ditukar.');
    }

    public function enableTwoFactor(Request $request, PortalTwoFactorService $service)
    {
        $user = auth('portal')->user();

        if (! $user->two_factor_secret) {
            $secret = $service->generateSecret();
            $user->two_factor_secret = $secret;
            $user->save();
        }

        $qrUrl = $service->getQrCodeUrl($user);

        return response()->json([
            'secret' => $user->two_factor_secret,
            'qrUrl' => $qrUrl,
        ]);
    }

    public function verifyTwoFactor(Request $request, PortalTwoFactorService $service)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);
        $user = auth('portal')->user();
        $valid = $service->enable($user, $request->code, $request);

        if (! $valid) {
            return response()->json(['ok' => false, 'message' => 'Kod tidak sah. Sila cuba lagi.'], 422);
        }

        return response()->json(['ok' => true, 'message' => '2FA berjaya diaktifkan.']);
    }

    public function disableTwoFactor(Request $request, PortalTwoFactorService $service)
    {
        $request->validate(['current_password' => ['required', 'string']]);
        $user = auth('portal')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Kata laluan tidak tepat.'], 422);
        }

        $service->disable($user, $request);

        return response()->json(['ok' => true, 'message' => '2FA telah dinyahaktifkan.']);
    }

    private function permissionsForRole(): array
    {
        return [
            ['label' => 'Dashboard', 'active' => true],
            ['label' => 'Permohonan', 'active' => true],
            ['label' => 'Had Kifayah', 'active' => true],
            ['label' => 'Profil', 'active' => true],
        ];
    }
}
