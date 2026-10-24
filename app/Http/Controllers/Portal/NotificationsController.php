<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(): View
    {
        $user = auth('portal')->user();

        $notifications = Notification::query()
            ->forUser($user)
            ->latest()
            ->paginate(20);

        $unread = Notification::query()
            ->forUser($user)
            ->unread()
            ->count();

        return view('portal.notifications.index', compact('user', 'notifications', 'unread'));
    }
}
