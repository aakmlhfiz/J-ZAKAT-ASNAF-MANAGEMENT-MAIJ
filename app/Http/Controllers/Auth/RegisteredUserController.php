<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\WelcomeAdminMail;
use App\Models\Admin;
use App\Models\AdminInvitation;
use App\Services\AuditLogger;
use App\Services\FirebaseService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('register');
    }

    public function store(RegisterRequest $request, FirebaseService $firebase)
    {
        $request->validate([
            'invitation_code' => ['required', 'string', 'max:64'],
        ]);

        $invitation = AdminInvitation::where('code', $request->invitation_code)->first();

        if (! $invitation) {
            return back()->withErrors(['invitation_code' => __('auth.errors.invitation_invalid')])->withInput();
        }

        if (! $invitation->isUsable()) {
            $reason = match (true) {
                $invitation->used_at !== null => __('auth.errors.invitation_used'),
                ! $invitation->is_active => __('auth.errors.invitation_inactive'),
                $invitation->expires_at && $invitation->expires_at->isPast() => __('auth.errors.invitation_expired'),
                default => __('auth.errors.invitation_unusable'),
            };

            return back()->withErrors(['invitation_code' => $reason])->withInput();
        }

        $firebaseUid = null;
        try {
            $firebaseUid = $firebase->createUser($request->email, $request->password, $request->name);
        } catch (\Exception $e) {
            Log::warning('Pendaftaran Firebase dilangkau — akaun admin tempatan sahaja akan dicipta.', ['error' => $e->getMessage()]);
        }

        DB::transaction(function () use ($request, $firebaseUid, $invitation) {
            $user = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'firebase_uid' => $firebaseUid,
            ]);

            $invitation->update([
                'used_by' => $user->id,
                'used_at' => now(),
            ]);

            event(new Registered($user));
            Auth::guard('web')->login($user);

            try {
                Mail::to($user->email)->send(new WelcomeAdminMail($user, $request->password));
            } catch (\Throwable $e) {
                Log::warning('Welcome emel gagal dihantar untuk pentadbir baharu.', [
                    'admin_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            AuditLogger::authLogin($user, $request);
        });

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('dashboard')]);
        }

        return redirect()->route('dashboard');
    }
}
