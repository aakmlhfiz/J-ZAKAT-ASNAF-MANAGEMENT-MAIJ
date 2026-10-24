<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ProfilAsnaf;
use Illuminate\View\View;

class LetterController extends Controller
{
    public function index(): View
    {
        $user = auth('portal')->user();

        $applications = $user->applications()
            ->whereIn('status', ['submitted', 'under_review', 'approved', 'rejected'])
            ->latest()
            ->get();

        $letters = $applications->map(function ($application) {
            $profil = ProfilAsnaf::where('ic', $application->ic_no)->first();

            return [
                'application' => $application,
                'has_letter' => $profil && $profil->letter_path,
                'letter_type' => $profil->letter_type ?? null,
                'letter_url' => $profil && $profil->letter_path ? route('user.applications.letter', $application->id) : null,
                'receipt_url' => route('user.applications.receipt', $application->id),
            ];
        });

        return view('portal.letters.index', compact('user', 'letters'));
    }
}
