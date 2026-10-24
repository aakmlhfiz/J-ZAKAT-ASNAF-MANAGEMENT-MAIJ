<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\SecurityLog;
use App\Services\ActivityLogger;
use App\Services\AuditLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth('web')->user();
        $userId = $user->id;

        $stats = [
            'login_count' => LoginLog::where('user_id', $userId)->successful()->count(),
            'last_login' => LoginLog::where('user_id', $userId)->successful()->latest()->first(),
            'account_created_date' => $user->created_at->format('d M Y'),
            'account_created_ago' => $user->created_at->diffForHumans(),
            'role_label' => $user->roleLabel(),
            'status_label' => $user->statusLabel(),
            'department' => $user->department ?? 'MAIJ',
        ];

        $completionPercent = $user->profileCompletion();
        $securityScore = $user->securityScore();

        $recentActivities = ActivityLog::forUser($userId)->recent(10)->get();
        $loginHistory = LoginLog::forUser($userId)->recent(20)->get();
        $securityLogs = SecurityLog::forUser($userId)->recent(10)->get();

        $currentSession = DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', session()->getId())
            ->first();

        $permissions = $this->permissionsForRole();

        return view('app.profile', compact(
            'user', 'stats', 'completionPercent', 'securityScore',
            'recentActivities', 'loginHistory', 'securityLogs',
            'currentSession', 'permissions'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = auth('web')->user();

        if ($request->hasFile('photo')) {
            $old = $user->profile_photo;

            $dir = 'profile/'.$user->id;
            Storage::disk('public')->makeDirectory($dir);

            $path = $request->file('photo')->storeAs(
                $dir,
                'avatar.'.$request->file('photo')->extension(),
                'public',
            );

            $user->profile_photo = $path;
            $user->save();

            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            ActivityLogger::photoUploaded($user, $request);
        }

        return back()->with('success', 'Gambar profil berjaya dikemaskini.');
    }

    public function updateInfo(Request $request)
    {
        $user = auth('web')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ic_no' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($value !== $user->$key) {
                $changes[$key] = ['old' => $user->$key, 'new' => $value];
            }
        }

        $user->fill($validated);
        $user->save();

        if (! empty($changes)) {
            ActivityLogger::profileUpdated($user, $changes, $request);
            AuditLogger::profileUpdated($user, $request, $changes);
        }

        return back()->with('success', 'Maklumat peribadi berjaya dikemaskini.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth('web')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata laluan semasa tidak tepat.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        ActivityLogger::passwordChanged($user, $request);
        AuditLogger::passwordChanged($user, $request);

        return back()->with('success', 'Kata laluan berjaya ditukar.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'dark_mode' => ['nullable', 'string', 'in:light,dark,system'],
            'language' => ['nullable', 'string', 'in:ms,en'],
            'notification_email' => ['nullable', 'boolean'],
            'notification_push' => ['nullable', 'boolean'],
            'notification_sms' => ['nullable', 'boolean'],
        ]);

        $user = auth('web')->user();
        $user->fill(array_filter($validated, fn ($v) => $v !== null));
        $user->save();

        ActivityLogger::settingsUpdated($user, 'profil', $request);

        return response()->json(['ok' => true]);
    }

    public function enableTwoFactor(Request $request)
    {
        $user = auth('web')->user();
        $service = app(TwoFactorService::class);

        if (! $user->two_factor_secret || $user->two_factor_secret === 'pending') {
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

    public function verifyTwoFactor(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = auth('web')->user();
        $service = app(TwoFactorService::class);

        $valid = $service->enable($user, $request->code, $request);

        if (! $valid) {
            return response()->json(['ok' => false, 'message' => 'Kod tidak sah. Sila cuba lagi.'], 422);
        }

        return response()->json(['ok' => true, 'message' => '2FA berjaya diaktifkan.']);
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate(['current_password' => ['required', 'string']]);
        $user = auth('web')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Kata laluan tidak tepat.'], 422);
        }

        $service = app(TwoFactorService::class);
        $service->disable($user, $request);

        return response()->json(['ok' => true, 'message' => '2FA telah dinyahaktifkan.']);
    }

    public function downloadData()
    {
        $user = auth('web')->user();

        $data = [
            'profile' => $user->only(['name', 'email', 'phone', 'ic_no', 'address', 'department', 'role', 'status']),
            'activities' => ActivityLog::forUser($user->id)->recent(200)->get()->toArray(),
            'logins' => LoginLog::forUser($user->id)->recent(100)->get()->toArray(),
            'security' => SecurityLog::forUser($user->id)->recent(100)->get()->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="account-data.json"',
        ]);
    }

    public function logoutOtherDevices(Request $request)
    {
        $user = auth('web')->user();
        $currentSessionId = session()->getId();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return response()->json(['ok' => true, 'message' => 'Semua peranti lain telah dilog keluar.']);
    }

    private function permissionsForRole(): array
    {
        return [
            ['label' => 'Dashboard', 'active' => true],
            ['label' => 'Permohonan', 'active' => true],
            ['label' => 'Semakan', 'active' => true],
            ['label' => 'Laporan', 'active' => true],
            ['label' => 'Tetapan', 'active' => true],
            ['label' => 'Import CSV', 'active' => true],
            ['label' => 'Had Kifayah', 'active' => true],
            ['label' => 'AI Assistant', 'active' => true],
        ];
    }
}
