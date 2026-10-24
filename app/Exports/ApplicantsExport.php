<?php

namespace App\Exports;

use App\Models\ProfilAsnaf;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ApplicantsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ApplicantsSummarySheet,
            new ApplicantsFullListSheet,
        ];
    }
}

class ApplicantsSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Ringkasan Bulanan';
    }

    public function collection()
    {
        $rows = collect();
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = now()->subMonths($i)->endOfMonth();
            $label = $start->format('M Y');
            $key = $start->format('Y-m');

            $month = ProfilAsnaf::whereBetween('created_at', [$start, $end])->get();
            $total = $month->count();
            $layak = $month->where('status_kelayakan', 'Layak')->count();
            $tidak = $month->where('status_kelayakan', 'Tidak Layak')->count();
            $semakan = $month->where('status_kelayakan', 'Dalam Semakan')->count();
            $pct = $total > 0 ? round(($layak / $total) * 100, 1) : 0;

            $rows->push([$label, $total, $layak, $tidak, $semakan, $pct.'%']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Bulan', 'Jumlah', 'Layak', 'Tidak Layak', 'Semakan', '% Layak'];
    }
}

class ApplicantsFullListSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Senarai Penuh';
    }

    public function collection()
    {
        return ProfilAsnaf::latest()->get();
    }

    public function headings(): array
    {
        return [
            'Nama', 'No IC', 'IC Ketua', 'Telefon', 'Alamat',
            'Pendapatan (RM)', 'Tanggungan', 'Sewa (RM)', 'Utiliti (RM)',
            'Had Kifayah (RM)', 'Status', 'AI Score',
        ];
    }

    public function map($p): array
    {
        return [
            $p->nama,
            $p->ic,
            $p->ic_ketua_rumah,
            $p->telefon,
            $p->alamat,
            (float) $p->pendapatan,
            (int) $p->bilangan_tanggungan,
            (float) $p->sewa,
            (float) $p->utiliti,
            (float) $p->had_kifayah,
            $p->status_kelayakan,
            (float) $p->ai_score,
        ];
    }
}
