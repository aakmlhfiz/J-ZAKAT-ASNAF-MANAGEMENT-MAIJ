<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AssistanceHistory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MoneyAuditController extends Controller
{
    public function index(Request $request): View
    {
        $ledgerQuery = $this->ledgerQuery($request);

        $trailQuery = AuditLog::query()->where('category', 'money');

        if ($request->filled('trail_user_id')) {
            $trailQuery->forUser((int) $request->trail_user_id);
        }
        if ($request->filled('trail_action')) {
            $trailQuery->action($request->trail_action);
        }
        if ($request->filled('trail_from') || $request->filled('trail_to')) {
            $trailQuery->betweenDates($request->trail_from, $request->trail_to);
        }
        if ($request->filled('trail_search')) {
            $s = $request->trail_search;
            $trailQuery->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                    ->orWhere('user_email', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $ledger = $ledgerQuery->latest('approved_date')->paginate(25)->withQueryString();
        $trail = $trailQuery->latest('created_at')->paginate(25, ['*'], 'trail_page')->withQueryString();

        $approved = AssistanceHistory::where('status', 'approved');

        $stats = [
            'total_amount' => (float) (clone $approved)->sum('amount'),
            'total_records' => AssistanceHistory::count(),
            'month_amount' => (float) (clone $approved)
                ->where('approved_date', '>=', now()->startOfMonth())
                ->where('approved_date', '<=', now()->endOfMonth())
                ->sum('amount'),
            'distinct_households' => AssistanceHistory::distinct('household_id')->count('household_id'),
        ];

        $byJenis = AssistanceHistory::query()
            ->selectRaw('jenis_bantuan, COUNT(*) as count, SUM(amount) as amount')
            ->where('status', 'approved')
            ->groupBy('jenis_bantuan')
            ->orderByDesc('amount')
            ->get();

        $users = Admin::orderBy('name')->get(['id', 'name', 'email']);
        $jenisOptions = array_keys(AssistanceHistory::jenisBantuanOptions());
        $trailActions = AuditLog::where('category', 'money')
            ->select('action')->distinct()->orderBy('action')->pluck('action');

        $tab = $request->query('tab', 'transaksi');

        return view('admin.money-audit.index', compact(
            'ledger', 'trail', 'stats', 'byJenis', 'users',
            'jenisOptions', 'trailActions', 'tab',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->ledgerQuery($request)->latest('approved_date');

        $filename = 'audit-kewangan-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                __('money_audit.export.id'),
                __('money_audit.export.approved_date'),
                __('money_audit.export.household'),
                __('money_audit.export.member'),
                __('money_audit.export.jenis_bantuan'),
                __('money_audit.export.amount'),
                __('money_audit.export.frequency'),
                __('money_audit.export.status'),
                __('money_audit.export.approval_officer'),
                __('money_audit.export.reference'),
                __('money_audit.export.description'),
            ]);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->approved_date?->toDateString(),
                        $r->household?->household_id,
                        $r->member?->nama,
                        $r->jenis_bantuan,
                        $r->amount,
                        $r->frequency,
                        $r->status,
                        $r->approval_officer,
                        $r->reference_number,
                        $r->description,
                    ]);
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function ledgerQuery(Request $request)
    {
        $query = AssistanceHistory::query()->with(['household', 'member']);

        if ($request->filled('jenis_bantuan')) {
            $query->where('jenis_bantuan', $request->jenis_bantuan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }
        if ($request->filled('from')) {
            $query->where('approved_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('approved_date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%{$s}%")
                    ->orWhere('approval_officer', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('household_id', 'like', "%{$s}%")
                    ->orWhereHas('household', fn ($h) => $h->where('household_id', 'like', "%{$s}%"))
                    ->orWhereHas('member', fn ($m) => $m->where('nama', 'like', "%{$s}%")->orWhere('ic', 'like', "%{$s}%"));
            });
        }

        return $query;
    }
}
