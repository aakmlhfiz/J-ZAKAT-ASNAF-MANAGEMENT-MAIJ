<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $notifications = Notification::query()
            ->forUser($user)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json($notifications);
    }

    public function unread(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $count = Notification::query()
            ->forUser($user)
            ->unread()
            ->count();

        $latest = Notification::query()
            ->forUser($user)
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $count,
            'notifications' => $latest,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $notification->markAsRead();

        $count = Notification::query()
            ->forUser($this->resolveUser($request))
            ->unread()
            ->count();

        return response()->json([
            'ok' => true,
            'count' => $count,
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::query()
            ->forUser($this->resolveUser($request))
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'count' => 0,
        ]);
    }

    private function resolveUser(Request $request)
    {
        return Auth::guard('web')->user() ?? Auth::guard('portal')->user() ?? $request->user();
    }
}
