<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query();

        if ($request->filled('user_id')) {
            $query->forUser((int) $request->user_id);
        }
        if ($request->filled('role')) {
            $query->forRole($request->role);
        }
        if ($request->filled('category')) {
            $query->category($request->category);
        }
        if ($request->filled('action')) {
            $query->action($request->action);
        }
        if ($request->filled('from') || $request->filled('to')) {
            $query->betweenDates($request->from, $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                    ->orWhere('user_email', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%")
                    ->orWhere('action', 'like', "%{$s}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'logins_today' => AuditLog::whereIn('action', ['admin.login', 'portal.login'])
                ->whereDate('created_at', today())->count(),
            'unique_users' => AuditLog::whereDate('created_at', today())
                ->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];

        $users = Admin::orderBy('name')->get(['id', 'name', 'email']);
        $categories = ['auth', 'application', 'data', 'security', 'system', 'money', 'audit'];
        $roles = ['admin', 'portal', 'system'];
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs.index', compact(
            'logs', 'stats', 'users', 'categories', 'roles', 'actions',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::query();

        if ($request->filled('user_id')) {
            $query->forUser((int) $request->user_id);
        }
        if ($request->filled('role')) {
            $query->forRole($request->role);
        }
        if ($request->filled('category')) {
            $query->category($request->category);
        }
        if ($request->filled('action')) {
            $query->action($request->action);
        }
        if ($request->filled('from') || $request->filled('to')) {
            $query->betweenDates($request->from, $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                    ->orWhere('user_email', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $filename = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                __('audit.export.id'),
                __('audit.export.created_at'),
                __('audit.export.user_id'),
                __('audit.export.user_role'),
                __('audit.export.user_email'),
                __('audit.export.category'),
                __('audit.export.action'),
                __('audit.export.description'),
                __('audit.export.ip_address'),
                __('audit.export.browser'),
                __('audit.export.device'),
                __('audit.export.os'),
                __('audit.export.metadata'),
            ]);
            $query->latest('created_at')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->created_at?->toIso8601String(),
                        $r->user_id,
                        $r->user_role,
                        $r->user_email,
                        $r->category,
                        $r->action,
                        $r->description,
                        $r->ip_address,
                        $r->browser,
                        $r->device,
                        $r->os,
                        $r->metadata ? json_encode($r->metadata, JSON_UNESCAPED_UNICODE) : '',
                    ]);
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
