<?php

namespace App\Http\Controllers;

use App\Models\ProfilAsnaf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Real-time global search across profil_asnafs.
     * Returns up to 5 matches by name or IC (case-insensitive).
     */
    public function global(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $q = trim($data['q']);

        $items = ProfilAsnaf::query()
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('ic', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->limit(5)
            ->get(['id', 'nama', 'ic', 'status_kelayakan', 'pendapatan', 'had_kifayah']);

        $results = $items->map(function ($p) {
            return [
                'id' => (int) $p->id,
                'nama' => (string) $p->nama,
                'ic' => (string) $p->ic,
                'status' => (string) ($p->status_kelayakan ?? 'Dalam Semakan'),
                'url' => route('viewprofile', $p->ic),
                'pendapatan' => (float) $p->pendapatan,
                'had_kifayah' => (float) $p->had_kifayah,
            ];
        })->values()->all();

        return response()->json([
            'q' => $q,
            'count' => count($results),
            'results' => $results,
        ]);
    }
}
