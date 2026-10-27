<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PortalUser;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PortalNewPasswordController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->email,
            'mode' => 'portal',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('portal_users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $userModel = PortalUser::where('email', $request->email)->first();
            if ($userModel) {
                AuditLogger::passwordReset($userModel, $request);
            }
        }

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('user.login')->with('status', __('auth.errors.reset_success'))
            : back()->withErrors(['email' => __('auth.errors.reset_invalid')]);
    }
}
