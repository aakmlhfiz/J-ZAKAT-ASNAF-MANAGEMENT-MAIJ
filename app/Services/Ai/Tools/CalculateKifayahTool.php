<?php

namespace App\Services\Ai\Tools;

use App\Services\HadAlKifayahService;

class CalculateKifayahTool extends AbstractTool
{
    public function matches(string $message): bool
    {
        return (bool) preg_match(
            '/\b(kira|calculate|compute|had\s*kifayah|kifayah|alkifayah|al-kifayah)\b/i',
            $message
        );
    }

    public function handle(string $message): array
    {
        $clean = preg_replace('/[^\d\s.,-]/u', ' ', $message);
        $clean = str_replace([',', 'RM', 'rm'], ' ', $clean);
        preg_match_all('/-?\d+(?:\.\d+)?/', $clean, $matches);
        $nums = array_map('floatval', $matches[0] ?? []);

        // Heuristic: first 4 numbers = pendapatan (pemohon, pasangan, anak, lain)
        // Next integers (≤ 20) = per-kategori counts (isteri/suami, ibu bapa, dewasa tk, dewasa bk, ipt, remaja, kanak_rendah, kanak_tadika)
        // Remaining numbers = perbelanjaan (utiliti, pengangkutan, perubatan)
        $pendapatan_pemohon = (float) ($nums[0] ?? 0);
        $pendapatan_pasangan = (float) ($nums[1] ?? 0);
        $pendapatan_anak = (float) ($nums[2] ?? 0);
        $pendapatan_lain = (float) ($nums[3] ?? 0);

        $rest = array_slice($nums, 4);
        $countKeys = [
            'n_isteri_suami', 'n_ibu_bapa', 'n_dewasa_tidak_bekerja', 'n_dewasa_bekerja_tanggung',
            'n_ipt', 'n_remaja', 'n_kanak_rendah', 'n_kanak_tadika',
        ];
        $counts = array_fill_keys($countKeys, 0);
        $i = 0;
        foreach ($rest as $n) {
            if ($i >= count($countKeys)) {
                break;
            }
            $v = (int) round($n);
            if ($v <= 20) {
                $counts[$countKeys[$i]] = $v;
                $i++;
            } else {
                break;
            }
        }

        $remaining = array_slice($rest, $i);
        $utiliti_input = (float) ($remaining[0] ?? 0);
        $pengangkutan_input = (float) ($remaining[1] ?? 0);
        $perubatan_input = (float) ($remaining[2] ?? 0);

        $r = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => $pendapatan_pemohon,
            'pendapatan_pasangan' => $pendapatan_pasangan,
            'pendapatan_anak' => $pendapatan_anak,
            'pendapatan_lain' => $pendapatan_lain,
        ] + $counts + [
            'perumahan' => 'sendiri',
            'utiliti_input' => $utiliti_input,
            'pengangkutan_input' => $pengangkutan_input,
            'perubatan_input' => $perubatan_input,
        ]);

        $formula = sprintf(
            'Had Kifayah = RM %.2f  (A − C = RM %.2f − RM %.2f)',
            $r['had_kifayah_total'],
            $r['pendapatan_total'],
            $r['had_kifayah_total']
        );

        return [
            'reply' => "Pengiraan Had Al-Kifayah (MAIDAM):\n\n**{$formula}**\n\nStatus: **{$r['status']}** (keyakinan AI: {$r['confidence']}%)",
            'data' => [
                'type' => 'eligibility',
                'title' => 'Keputusan Kelayakan',
                'status' => $r['status'],
                'confidence' => $r['confidence'],
                'pendapatan' => $r['pendapatan_total'],
                'had_kifayah' => $r['had_kifayah_total'],
                'baki' => $r['baki'],
                'ai_score' => $r['confidence'],
                'items' => array_map(
                    fn ($row) => $row['label'].': RM '.number_format($row['subtotal'], 2),
                    $r['breakdown']
                ),
            ],
        ];
    }

    public function label(): string
    {
        return 'Kira Had Kifayah';
    }

    public function icon(): string
    {
        return 'fa-calculator';
    }
}
