<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PortalUser;
use App\Services\ActivityLogger;
use App\Services\AuditLogger;
use App\Services\PortalTwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalLoginController extends Controller
{
    public function create()
    {
        return view('login', ['mode' => 'portal']);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('portal')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('portal')->user();

            if (! $user->isActive()) {
                Auth::guard('portal')->logout();

                $message = $user->status === 'suspended'
                    ? 'Akaun anda telah digantung. Sila hubungi pihak MAIJ.'
                    : 'Akaun anda tidak aktif. Sila hubungi pihak MAIJ.';

                if ($request->wantsJson()) {
                    return response()->json(['errors' => ['email' => [$message]]], 422);
                }

                return back()->withErrors(['email' => $message])->onlyInput('email');
            }

            if ($user->hasTwoFactorEnabled()) {
                Auth::guard('portal')->logout();
                $request->session()->put('auth.2fa.user_id', $user->id);
                $request->session()->put('auth.2fa.portal', true);
                $request->session()->put('auth.2fa.remember', $request->boolean('remember'));

                if ($request->wantsJson()) {
                    return response()->json(['redirect' => route('user.2fa.challenge')]);
                }

                return redirect()->route('user.2fa.challenge');
            }

            $request->session()->regenerate();
            AuditLogger::authLogin($user, $request);

            if ($request->wantsJson()) {
                return response()->json(['redirect' => route('user.dashboard')]);
            }

            return redirect()->intended(route('user.dashboard'));
        }

        if ($request->wantsJson()) {
            ActivityLogger::failedLogin($request->email, $request);
            AuditLogger::authLogin(null, $request, 'failed');

            return response()->json(['errors' => ['email' => ['Email atau kata laluan tidak sah.']]], 422);
        }

        ActivityLogger::failedLogin($request->email, $request);
        AuditLogger::authLogin(null, $request, 'failed');

        return back()->withErrors([
            'email' => 'Email atau kata laluan tidak sah.',
        ])->onlyInput('email');
    }

    public function showChallenge(Request $request)
    {
        if (! session('auth.2fa.user_id') || ! session('auth.2fa.portal')) {
            return redirect()->route('user.login');
        }

        return view('auth.2fa-challenge', ['mode' => 'portal']);
    }

    public function verifyChallenge(Request $request, PortalTwoFactorService $service)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = session('auth.2fa.user_id');
        $isPortal = session('auth.2fa.portal', false);

        if (! $userId || ! $isPortal) {
            return redirect()->route('user.login');
        }

        $user = PortalUser::find($userId);
        if (! $user) {
            return redirect()->route('user.login');
        }

        $attempts = (int) $request->session()->get('auth.2fa.attempts', 0);
        if ($attempts >= 5) {
            $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.portal', 'auth.2fa.remember', 'auth.2fa.attempts']);

            return back()->withErrors(['code' => 'Terlalu banyak percubaan. Sila log masuk semula.']);
        }

        if (! $user->isActive()) {
            $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.portal', 'auth.2fa.remember']);

            return back()->withErrors(['code' => 'Akaun anda tidak aktif. Sila hubungi pihak MAIJ.']);
        }

        if (! $service->verifyCode($user->two_factor_secret, $request->code)) {
            $request->session()->put('auth.2fa.attempts', $attempts + 1);

            return back()->withErrors(['code' => 'Kod 2FA tidak sah. Sila cuba lagi.']);
        }

        Auth::guard('portal')->login($user, session('auth.2fa.remember', false));
        $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.portal', 'auth.2fa.remember', 'auth.2fa.attempts']);
        $request->session()->regenerate();

        AuditLogger::authLogin($user, $request);

        return redirect()->intended(route('user.dashboard'));
    }

    public function cancelChallenge(Request $request)
    {
        $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.portal', 'auth.2fa.remember']);

        return redirect()->route('user.login');
    }

    public function destroy(Request $request)
    {
        $user = Auth::guard('portal')->user();
        if ($user) {
            AuditLogger::authLogout($user, $request);
        }

        Auth::guard('portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }
}
