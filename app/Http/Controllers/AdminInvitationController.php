<?php

namespace App\Http\Controllers;

use App\Models\AdminInvitation;
use Illuminate\Http\Request;

class AdminInvitationController extends Controller
{
    private function isSuperAdmin(): bool
    {
        $email = auth('web')->user()?->email;

        return $email !== null && in_array($email, (array) config('app.super_admin_emails'), true);
    }

    public function index(Request $request)
    {
        $query = AdminInvitation::with(['creator', 'user'])->latest();

        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->available(),
                'expired' => $query->expired(),
                'used' => $query->used(),
                'deactivated' => $query->where('is_active', false)->whereNull('used_at'),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        }

        $invitations = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => AdminInvitation::count(),
            'active' => AdminInvitation::available()->count(),
            'used' => AdminInvitation::used()->count(),
            'expired' => AdminInvitation::expired()->count(),
        ];

        $isSuperAdmin = $this->isSuperAdmin();

        return view('admin.invitations.index', compact('invitations', 'stats', 'isSuperAdmin'));
    }

    public function create()
    {
        abort_unless($this->isSuperAdmin(), 403);

        return view('admin.invitations.create');
    }

    public function store(Request $request)
    {
        abort_unless($this->isSuperAdmin(), 403);
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $invitation = AdminInvitation::create([
            'code' => AdminInvitation::generateCode(),
            'created_by' => auth('web')->id(),
            'notes' => $validated['notes'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.invitations.show', $invitation)
            ->with('success', 'Kod undangan baharu berjaya dijana.');
    }

    public function show(AdminInvitation $invitation)
    {
        abort_unless($this->isSuperAdmin(), 403);
        $invitation->load(['creator', 'user']);
        $isSuperAdmin = $this->isSuperAdmin();

        return view('admin.invitations.show', compact('invitation', 'isSuperAdmin'));
    }

    public function destroy(AdminInvitation $invitation)
    {
        abort_unless($this->isSuperAdmin(), 403);
        if ($invitation->used_at) {
            return back()->with('error', 'Kod yang telah digunakan tidak boleh dipadam.');
        }

        $invitation->delete();

        return redirect()->route('admin.invitations.index')
            ->with('success', 'Kod undangan berjaya dipadam.');
    }

    public function deactivate(AdminInvitation $invitation)
    {
        abort_unless($this->isSuperAdmin(), 403);
        if ($invitation->used_at) {
            return back()->with('error', 'Kod yang telah digunakan tidak boleh dinyahaktifkan.');
        }

        $invitation->update(['is_active' => false]);

        return back()->with('success', 'Kod undangan telah dinyahaktifkan.');
    }

    public function reactivate(AdminInvitation $invitation)
    {
        abort_unless($this->isSuperAdmin(), 403);
        if ($invitation->used_at) {
            return back()->with('error', 'Kod yang telah digunakan tidak boleh diaktifkan semula.');
        }

        $invitation->update(['is_active' => true]);

        return back()->with('success', 'Kod undangan telah diaktifkan semula.');
    }
}
