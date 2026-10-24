<?php

namespace App\Http\Controllers;

use App\Models\ProfilAsnaf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProfilAsnaf::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('ic', 'like', "%{$search}%")
                    ->orWhere('ic_ketua_rumah', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status_kelayakan', $status);
        }

        $items = $query->latest()->paginate(15)->withQueryString();

        $base = ProfilAsnaf::query();
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('ic', 'like', "%{$search}%")
                    ->orWhere('ic_ketua_rumah', 'like', "%{$search}%");
            });
        }

        $totals = [
            'all' => (clone $base)->count(),
            'layak' => (clone $base)->where('status_kelayakan', 'Layak')->count(),
            'tidak_layak' => (clone $base)->where('status_kelayakan', 'Tidak Layak')->count(),
            'semakan' => (clone $base)->where('status_kelayakan', 'Dalam Semakan')->count(),
        ];

        return view('history', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'totals' => $totals,
        ]);
    }
}
