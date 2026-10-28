<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\PortalUser;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PortalPasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password', ['mode' => 'portal']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::broker('portal_users')->sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                Mail::to($user->email)->send(new ResetPasswordMail(
                    token: $token,
                    name: $user->name,
                    email: $user->email,
                    resetUrl: url(route('user.password.reset', ['token' => $token, 'email' => $user->email], false)),
                    locale: $user->language,
                ));
            }
        );

        if ($status === Password::RESET_LINK_SENT) {
            $portal = PortalUser::where('email', $request->email)->first();
            if ($portal) {
                AuditLogger::passwordReset($portal, $request);
            }
        }

        return back()->with('status', __('auth.errors.reset_sent'));
    }
}
