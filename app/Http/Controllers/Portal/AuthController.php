<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\RegisterRequest;
use App\Mail\NewApplicantRegisteredMail;
use App\Mail\PortalUserWelcomeMail;
use App\Models\Admin;
use App\Models\PortalUser;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('portal.auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $user = PortalUser::create([
            'name' => $request->name,
            'ic_no' => $request->ic_no,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'district' => $request->district,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::guard('portal')->login($user);

        AuditLogger::profileUpdated($user, $request, ['event' => 'portal_user_created']);

        foreach (Admin::all() as $admin) {
            try {
                Mail::to($admin->email)->send(new NewApplicantRegisteredMail($user));
            } catch (\Throwable $e) {
                Log::warning('Gagal menghantar emel pemohon baharu kepada pentadbir.', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'new_applicant_registered',
            'Pemohon Baharu Berdaftar',
            "{$user->name} · {$user->ic_no}",
            'fa-solid fa-user-plus',
            'success',
            null,
        );

        try {
            Mail::to($user->email)->send(new PortalUserWelcomeMail($user));
        } catch (\Throwable $e) {
            Log::warning('Gagal menghantar emel selamat datang kepada pemohon baharu.', [
                'portal_user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('user.dashboard')]);
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Selamat datang! Akaun anda telah berjaya dicipta.');
    }
}
