<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\ActivityLogger;
use App\Services\AuditLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('login', ['mode' => 'admin']);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('web')->user();

            if ($user->hasTwoFactorEnabled()) {
                Auth::guard('web')->logout();
                $request->session()->put('auth.2fa.user_id', $user->id);
                $request->session()->put('auth.2fa.remember', $request->boolean('remember'));

                if ($request->wantsJson()) {
                    return response()->json(['redirect' => route('auth.2fa.challenge')]);
                }

                return redirect()->route('auth.2fa.challenge');
            }

            $request->session()->regenerate();
            ActivityLogger::login($user, $request);
            AuditLogger::authLogin($user, $request);

            $redirect = route('dashboard');

            if ($request->wantsJson()) {
                return response()->json(['redirect' => $redirect]);
            }

            return redirect()->to($redirect);
        }

        ActivityLogger::failedLogin($request->email, $request);
        AuditLogger::authLogin(null, $request, 'failed');

        if ($request->wantsJson()) {
            return response()->json(['errors' => ['email' => [__('auth.errors.invalid_credentials')]]], 422);
        }

        return back()->withErrors([
            'email' => __('auth.errors.invalid_credentials'),
        ])->onlyInput('email');
    }

    public function showChallenge()
    {
        if (! session('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        if (session('auth.2fa.portal') === true) {
            return redirect()->route('user.2fa.challenge');
        }

        return view('auth.2fa-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = session('auth.2fa.user_id');
        $isPortal = session('auth.2fa.portal', false);

        if (! $userId) {
            return redirect()->route($isPortal ? 'user.login' : 'login');
        }

        if ($isPortal) {
            return redirect()->route('user.2fa.verify');
        }

        $user = Admin::find($userId);
        if (! $user) {
            return redirect()->route('login');
        }

        $attempts = (int) session()->get('auth.2fa.attempts', 0);
        if ($attempts >= 5) {
            session()->forget(['auth.2fa.user_id', 'auth.2fa.remember', 'auth.2fa.attempts']);

            return back()->withErrors(['code' => __('auth.errors.too_many_attempts')]);
        }

        $service = app(TwoFactorService::class);
        if (! $service->verifyCode($user->two_factor_secret, $request->code)) {
            session()->put('auth.2fa.attempts', $attempts + 1);

            return back()->withErrors(['code' => __('auth.errors.invalid_2fa')]);
        }

        Auth::guard('web')->login($user, session('auth.2fa.remember', false));
        session()->forget(['auth.2fa.user_id', 'auth.2fa.remember', 'auth.2fa.attempts']);
        $request->session()->regenerate();

        ActivityLogger::login($user, $request);
        AuditLogger::authLogin($user, $request);

        return redirect()->intended(route('dashboard'));
    }

    public function cancelChallenge(Request $request)
    {
        $isPortal = session('auth.2fa.portal', false);
        session()->forget(['auth.2fa.user_id', 'auth.2fa.portal', 'auth.2fa.remember']);

        return redirect()->route($isPortal ? 'user.login' : 'login');
    }

    public function destroy(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            ActivityLogger::logout($user, $request);
            AuditLogger::authLogout($user, $request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
