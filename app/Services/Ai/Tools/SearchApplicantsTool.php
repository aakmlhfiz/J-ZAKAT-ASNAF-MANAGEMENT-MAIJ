<?php

namespace App\Services\Ai\Tools;

use App\Models\ProfilAsnaf;

/**
 * Searches applicants by name. Triggered by "cari" + name or "nama <x>".
 */
class SearchApplicantsTool extends AbstractTool
{
    public function matches(string $message): bool
    {
        return (bool) preg_match('/\b(cari|cari\s+rekod|cari\s+pemohon|find|search|search\s+for)\b/i', $message)
            || (preg_match('/\bnama\s+([a-zA-Z\s]{3,})/i', $message) && ! str_contains($message, 'penuh'));
    }

    public function handle(string $message): array
    {
        // Extract a search term.
        $query = trim($message);
        $query = preg_replace('/^(cari|cari\s+rekod|cari\s+pemohon|find|search(\s+for)?|nama)\s+/i', '', $query);
        $query = trim($query, " \t\n\r\0\x0B?");

        if ($query === '' || mb_strlen($query) < 2) {
            return [
                'reply' => 'Sila berikan nama atau sebahagian nama untuk dicari. Contoh: *cari Ahmad*.',
                'data' => ['type' => 'text'],
            ];
        }

        $results = ProfilAsnaf::where('nama', 'like', "%{$query}%")
            ->orWhere('ic', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            return [
                'reply' => "Tiada pemohon dijumpai untuk carian **{$query}**.",
                'data' => ['type' => 'text', 'query' => $query, 'not_found' => true],
            ];
        }

        $list = $results->map(fn ($p) => [
            'nama' => $p->nama,
            'ic' => $p->ic,
            'status' => $p->status_kelayakan,
            'profile' => route('viewprofile', $p->ic),
        ])->values()->all();

        return [
            'reply' => "Ditemui **{$results->count()}** pemohon untuk carian **{$query}**:",
            'data' => [
                'type' => 'search_results',
                'query' => $query,
                'count' => $results->count(),
                'results' => $list,
            ],
        ];
    }

    public function label(): string
    {
        return 'Cari rekod pemohon';
    }

    public function icon(): string
    {
        return 'fa-magnifying-glass';
    }
}
