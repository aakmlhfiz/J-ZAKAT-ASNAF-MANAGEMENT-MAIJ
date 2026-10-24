<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssistanceHistoryController extends Controller
{
    public function store(Request $request, Household $household): RedirectResponse
    {
        $data = $this->validated($request, $household);

        $history = $household->assistanceHistories()->create($data);

        AuditLogger::moneyTransactionCreated(
            $this->admin(),
            $history->load('household', 'member'),
            $request
        );

        return redirect()->route('household.show', $household->household_id)
            ->with('success', 'Rekod bantuan baharu disimpan.');
    }

    public function update(Request $request, AssistanceHistory $assistance): RedirectResponse
    {
        $household = $assistance->household;
        $data = $this->validated($request, $household);

        $changes = $this->diff($assistance->only([
            'household_member_id', 'jenis_bantuan', 'amount', 'frequency', 'status',
            'approved_date', 'expiry_date', 'approval_officer', 'reference_number', 'description',
        ]), $data);

        $assistance->update($data);

        AuditLogger::moneyTransactionUpdated(
            $this->admin(),
            $assistance->load('household', 'member'),
            $changes,
            $request
        );

        return redirect()->route('household.show', $household->household_id)
            ->with('success', 'Rekod bantuan dikemaskini.');
    }

    public function destroy(Request $request, AssistanceHistory $assistance): RedirectResponse
    {
        $household = $assistance->household;

        AuditLogger::moneyTransactionDeleted(
            $this->admin(),
            $assistance->load('household', 'member'),
            $request
        );

        $assistance->delete();

        return redirect()->route('household.show', $household->household_id)
            ->with('success', 'Rekod bantuan dipadam.');
    }

    private function validated(Request $request, Household $household): array
    {
        $jenisOptions = array_keys(AssistanceHistory::jenisBantuanOptions());

        return $request->validate([
            'household_member_id' => ['required', 'integer',
                function ($attribute, $value, $fail) use ($household) {
                    $exists = HouseholdMember::where('household_id', $household->id)
                        ->whereKey((int) $value)->exists();
                    if (! $exists) {
                        $fail('Ahli isi rumah tidak sah.');
                    }
                },
            ],
            'jenis_bantuan' => ['required', 'string', 'in:'.implode(',', $jenisOptions)],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'frequency' => ['required', 'string', 'in:one_off,monthly'],
            'status' => ['required', 'string', 'in:approved,rejected,pending'],
            'approved_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:approved_date'],
            'approval_officer' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function diff(array $before, array $after): array
    {
        $changes = [];

        foreach ($before as $key => $value) {
            $afterValue = $after[$key] ?? null;
            if ($this->normalize($value) !== $this->normalize($afterValue)) {
                $changes[$key] = ['from' => $value, 'to' => $afterValue];
            }
        }

        return $changes;
    }

    private function normalize(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value === null ? null : (string) $value;
    }

    private function admin(): Admin
    {
        return auth('web')->user();
    }
}
