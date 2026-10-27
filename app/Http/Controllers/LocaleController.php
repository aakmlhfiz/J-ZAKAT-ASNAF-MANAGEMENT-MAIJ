<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(SetLocale::SUPPORTED)],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);

        $user = $request->user('web') ?? $request->user('portal');

        if ($user && $user->language !== $locale) {
            $user->forceFill(['language' => $locale])->save();
        }

        App::setLocale($locale);

        return back();
    }
}
