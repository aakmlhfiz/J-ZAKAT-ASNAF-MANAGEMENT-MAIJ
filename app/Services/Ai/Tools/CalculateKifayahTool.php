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

        return $this->calculate([
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
    }

    /**
     * Run the calculator with structured input from a function-calling model.
     * Missing keys are treated as zero by HadAlKifayahService.
     */
    public function calculate(array $input): array
    {
        $r = HadAlKifayahService::calculate($input);

        $formula = __('ai.kifayah.formula', [
            'had_kifayah' => number_format($r['had_kifayah_total'], 2),
            'income' => number_format($r['pendapatan_total'], 2),
        ]);

        return [
            'reply' => __('ai.tools.calculate_kifayah.reply', [
                'formula' => $formula,
                'status' => $this->statusLabel($r['status']),
                'confidence' => $r['confidence'],
            ]),
            'data' => [
                'type' => 'eligibility',
                'title' => __('ai.card.eligibility_result'),
                'status' => $r['status'],
                'confidence' => $r['confidence'],
                'pendapatan' => $r['pendapatan_total'],
                'had_kifayah' => $r['had_kifayah_total'],
                'baki' => $r['baki'],
                'ai_score' => $r['confidence'],
                'items' => array_map(
                    fn ($row) => $this->localizeBreakdownLabel($row['label']).': RM '.number_format($row['subtotal'], 2),
                    $r['breakdown']
                ),
            ],
        ];
    }

    /**
     * Translate a Had Kifayah breakdown label produced by HadAlKifayahService.
     * Labels keep their " × count" suffix; only the base name is translated.
     */
    private function localizeBreakdownLabel(string $label): string
    {
        if (str_contains($label, ' × ')) {
            [$base, $count] = explode(' × ', $label, 2);

            return $this->baseBreakdownLabel(trim($base)).' × '.$count;
        }

        return $this->baseBreakdownLabel($label);
    }

    private function baseBreakdownLabel(string $base): string
    {
        if (str_starts_with($base, 'Rumah Sewa')) {
            return str_contains($base, 'tidak')
                ? __('ai.kifayah.rent_not_selected')
                : __('ai.kifayah.rent_selected');
        }

        return match ($base) {
            'Ketua Keluarga' => __('ai.kifayah.head'),
            'Isteri / Suami' => __('ai.kifayah.spouse'),
            'Ibu Bapa Tanggungan' => __('ai.kifayah.parents'),
            'Dewasa Tidak Bekerja' => __('ai.kifayah.adult_unemployed'),
            'Dewasa Bekerja Ditanggung' => __('ai.kifayah.adult_working_dependant'),
            'IPT' => __('ai.kifayah.higher_education'),
            'Remaja' => __('ai.kifayah.teenager'),
            'Sekolah Rendah' => __('ai.kifayah.primary_school'),
            'Tadika' => __('ai.kifayah.kindergarten'),
            'Utiliti (input)' => __('ai.kifayah.utilities'),
            'Pengangkutan (input)' => __('ai.kifayah.transport'),
            'Perubatan (input)' => __('ai.kifayah.medical'),
            default => $base,
        };
    }

    public function label(): string
    {
        return __('ai.tools.calculate_kifayah.label');
    }

    public function icon(): string
    {
        return 'fa-calculator';
    }
}
