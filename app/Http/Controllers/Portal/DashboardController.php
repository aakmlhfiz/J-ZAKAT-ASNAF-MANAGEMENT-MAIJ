<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('portal')->user();
        $applications = $user->applications()->latest()->get();
        $stats = [
            'total' => $applications->count(),
            'draft' => $applications->where('status', 'draft')->count(),
            'submitted' => $applications->where('status', 'submitted')->count(),
            'under_review' => $applications->whereIn('status', ['under_review'])->count(),
            'approved' => $applications->where('status', 'approved')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];

        $latestApplication = $applications->first();

        $unreadNotifications = Notification::query()
            ->forUser($user)
            ->unread()
            ->count();

        $hasData = $stats['total'] > 0;

        $trendLabels = __('common.months_short');
        $trendData = $hasData
            ? $this->getRealTrend($applications)
            : [3, 5, 4, 7, 6, 9, 8, 12, 10, 14, 11, 15];

        $statusData = [
            __('applications.status.draft') => $stats['draft'],
            __('applications.status.submitted') => $stats['submitted'],
            __('applications.status.under_review') => $stats['under_review'],
            __('applications.status.approved') => $stats['approved'],
            __('applications.status.rejected') => $stats['rejected'],
        ];

        return view('portal.dashboard', compact(
            'user', 'applications', 'stats', 'hasData',
            'trendLabels', 'trendData', 'statusData',
            'latestApplication', 'unreadNotifications'
        ));
    }

    private function getRealTrend($applications)
    {
        $counts = array_fill(0, 12, 0);
        foreach ($applications as $app) {
            $month = $app->created_at->month - 1;
            $counts[$month]++;
        }

        return $counts;
    }
}
