<?php

namespace App\Http\Controllers;

use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Services\HouseholdAiService;
use App\Services\HouseholdDuplicateService;
use App\Services\HouseholdInsightService;
use App\Services\HouseholdService;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    public function __construct(
        protected HouseholdService $householdService,
        protected HouseholdAiService $aiService,
        protected HouseholdDuplicateService $duplicateService,
        protected HouseholdInsightService $insightService,
    ) {}

    public function dashboard(Request $request)
    {
        $filter = $request->query('filter');
        $query = Household::with(['members', 'assistanceHistories']);

        if ($filter) {
            $query = $this->applyFilter($query, $filter);
        }

        $households = $query->latest()->paginate(12)->withQueryString();

        $totals = [
            'total_households' => Household::count(),
            'total_members' => HouseholdMember::count(),
            'total_assistance' => number_format(AssistanceHistory::where('status', 'approved')->sum('amount'), 2),
            'active_assistance' => AssistanceHistory::where('status', 'approved')->count(),
            'eligible' => Household::where('eligibility_status', 'Layak')->count(),
            'high_ai' => Household::where('ai_score', '>=', 75)->count(),
        ];

        return view('household.dashboard', compact('households', 'totals', 'filter'));
    }

    public function search(Request $request)
    {
        $ic = trim((string) $request->query('ic', ''));
        $result = null;

        if ($ic) {
            $result = $this->householdService->searchByIC($ic);
        }

        return view('household.search', compact('result', 'ic'));
    }

    public function show(string $id)
    {
        $household = $this->householdService->findByHouseholdId($id);

        if (! $household) {
            abort(404, 'Isi rumah tidak dijumpai.');
        }

        $aiAnalysis = $this->aiService->calculateHouseholdScore($household);
        $duplicateCheck = $this->duplicateService->detectDuplicates($household);
        $insights = $this->insightService->generate($household);
        $benefitMatrix = $this->householdService->getBenefitMatrix($household);

        $historyByYear = $household->assistanceHistories()
            ->with('member')
            ->orderBy('approved_date', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->approved_date?->format('Y') ?? 'Tiada Tahun');

        $activeTab = request()->query('tab', 'members');

        return view('household.show', compact(
            'household', 'aiAnalysis', 'duplicateCheck', 'insights',
            'benefitMatrix', 'historyByYear', 'activeTab'
        ));
    }

    public function apiSearch(Request $request)
    {
        $raw = trim((string) $request->query('q', ''));
        if (mb_strlen($raw) < 2) {
            return response()->json(['query' => '', 'count' => 0, 'results' => []]);
        }

        $ic = preg_replace('/[\s\-]/', '', $raw);

        $members = HouseholdMember::query()
            ->with(['household' => fn ($q) => $q->with('ketua')])
            ->where(function ($w) use ($raw, $ic) {
                $w->where('ic', 'like', "%{$ic}%")
                    ->orWhere('nama', 'like', "%{$raw}%")
                    ->orWhere('hubungan', 'like', "%{$raw}%")
                    ->orWhereHas('household', function ($h) use ($raw) {
                        $h->where('household_id', 'like', "%{$raw}%")
                            ->orWhere('daerah', 'like', "%{$raw}%")
                            ->orWhere('mukim', 'like', "%{$raw}%");
                    });
            })
            ->limit(8)
            ->get();

        $results = $members->map(function ($m) {
            $h = $m->household;
            $ketua = $h?->ketua;

            return [
                'name' => $m->nama,
                'ic' => $m->ic,
                'hubungan' => $m->hubungan,
                'household_id' => $h?->household_id,
                'household_name' => $ketua?->nama ?? 'N/A',
                'daerah' => $h?->daerah,
                'mukim' => $h?->mukim,
                'eligibility_status' => $h?->eligibility_status,
                'ai_score' => $h?->ai_score ? (int) round($h->ai_score) : null,
                'members_count' => $h ? $h->members()->count() : 0,
                'url' => $h ? route('household.show', $h->household_id) : null,
            ];
        })->filter(fn ($r) => $r['url'])->values();

        return response()->json([
            'query' => $raw,
            'count' => $results->count(),
            'results' => $results,
        ]);
    }

    public function apiStats()
    {
        return response()->json([
            'total_households' => (int) Household::count(),
            'total_members' => (int) HouseholdMember::count(),
            'total_assistance' => number_format(AssistanceHistory::where('status', 'approved')->sum('amount'), 2),
            'active_assistance' => (int) AssistanceHistory::where('status', 'approved')->count(),
            'eligible' => (int) Household::where('eligibility_status', 'Layak')->count(),
            'high_ai' => (int) Household::where('ai_score', '>=', 75)->count(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function applyFilter($query, string $filter)
    {
        return match ($filter) {
            'rental' => $query->whereHas('members.assistanceHistories', fn ($q) => $q->where('jenis_bantuan', 'Sewa Rumah')),
            'dialysis' => $query->whereHas('members.assistanceHistories', fn ($q) => $q->where('jenis_bantuan', 'Dialisis')),
            'single_mother' => $query->whereHas('members', fn ($q) => $q->where('hubungan', 'Ibu Tunggal')),
            'ipt' => $query->whereHas('members.assistanceHistories', fn ($q) => $q->where('jenis_bantuan', 'IPT')),
            'oku' => $query->whereHas('members', fn ($q) => $q->where('status_kesihatan', 'like', '%OKU%')),
            'elderly' => $query->whereHas('members', fn ($q) => $q->where('umur', '>=', 60)),
            'high_ai' => $query->where('ai_score', '>=', 75),
            'eligible' => $query->where('eligibility_status', 'Layak'),
            'approved' => $query->whereHas('assistanceHistories', fn ($q) => $q->where('status', 'approved')),
            'rejected' => $query->whereHas('assistanceHistories', fn ($q) => $q->where('status', 'rejected')),
            default => $query,
        };
    }
}
