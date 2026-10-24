<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Admin;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password', ['mode' => 'admin']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                Mail::to($user->email)->send(new ResetPasswordMail(
                    token: $token,
                    name: $user->name,
                    email: $user->email,
                    resetUrl: url(route('password.reset', ['token' => $token, 'email' => $user->email], false)),
                ));
            }
        );

        if ($status === Password::RESET_LINK_SENT) {
            $admin = Admin::where('email', $request->email)->first();
            if ($admin) {
                AuditLogger::passwordReset($admin, $request);
            }
        }

        return back()->with('status', 'Jika akaun wujud, pautan set semula kata laluan telah dihantar ke emel anda.');
    }
}
