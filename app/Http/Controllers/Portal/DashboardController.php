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

        $trendLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
        $trendData = $hasData
            ? $this->getRealTrend($applications)
            : [3, 5, 4, 7, 6, 9, 8, 12, 10, 14, 11, 15];

        $statusData = [
            'Draf' => $stats['draft'],
            'Dihantar' => $stats['submitted'],
            'Dalam Semakan' => $stats['under_review'],
            'Diluluskan' => $stats['approved'],
            'Ditolak' => $stats['rejected'],
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
