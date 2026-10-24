<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\ProfilAsnaf;
use App\Services\HouseholdAiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HouseholdBackfill extends Command
{
    protected $signature = 'household:backfill {--dry-run : Simulate without saving}';

    protected $description = 'Backfill profil_asnafs and applications into household tables';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;
        $dry = $this->option('dry-run');

        if ($dry) {
            $this->warn('DRY RUN — no changes will be saved.');
        }

        $profilGroups = $this->groupProfilAsnafs();

        foreach ($profilGroups as $icKetua => $members) {

            $existingMember = HouseholdMember::withTrashed()->where('ic', $icKetua)->first();
            if ($existingMember) {
                $this->line("  <comment>∎</comment> Household for IC <info>{$icKetua}</info> already exists — skipping.");
                $skipped++;

                continue;
            }

            $ketua = $members->firstWhere('ic', $icKetua)
                ?? $members->first();

            if ($dry) {
                $this->line("  <fg=green>+</> Would create household: <info>{$ketua->nama}</info> ({$ketua->ic}) with ".$members->count().' members');
                $created++;

                continue;
            }

            $household = Household::create([
                'alamat' => $ketua->alamat,
                'total_income' => $ketua->pendapatan ?? 0,
                'total_expenses' => ($ketua->sewa ?? 0) + ($ketua->utiliti ?? 0)
                    + ($ketua->pengangkutan_input ?? 0) + ($ketua->perubatan_input ?? 0),
                'ai_score' => $ketua->ai_score ?? 0,
                'eligibility_status' => $ketua->status_kelayakan ?? 'Dalam Semakan',
                'status' => 'active',
            ]);

            foreach ($members as $profil) {
                HouseholdMember::create([
                    'household_id' => $household->id,
                    'profil_asnaf_id' => $profil->id,
                    'nama' => $profil->nama,
                    'ic' => $profil->ic,
                    'hubungan' => $this->mapHubungan($profil, $icKetua),
                    'umur' => $this->calcAge($profil->ic),
                    'pekerjaan' => null,
                    'pendidikan' => $this->guessPendidikan($profil),
                    'pendapatan_bulanan' => $profil->pendapatan ?? 0,
                    'status_kesihatan' => null,
                    'applicant_status' => 'active',
                    'assistance_status' => $profil->approval_status === 'approved' ? 'Menerima Bantuan' : null,
                ]);

                if ($profil->approval_status === 'approved') {
                    AssistanceHistory::create([
                        'household_id' => $household->id,
                        'household_member_id' => $household->members()->where('ic', $profil->ic)->first()->id,
                        'jenis_bantuan' => 'Sara Hidup',
                        'amount' => $profil->had_kifayah > 0 ? $profil->had_kifayah - ($profil->pendapatan ?? 0) : 300.00,
                        'frequency' => 'monthly',
                        'status' => 'approved',
                        'approved_date' => $profil->approved_at ?? now(),
                        'approval_officer' => $profil->approvedBy?->name ?? 'Sistem',
                        'reference_number' => 'MAIJ-BACKFILL-'.str_pad($profil->id, 4, '0', STR_PAD_LEFT),
                        'description' => 'Data diimport dari profil asnaf sedia ada',
                    ]);
                }
            }

            (new HouseholdAiService)->calculateHouseholdScore($household);

            $this->line("  <fg=green>✓</> <info>{$household->household_id}</info> — {$ketua->nama} (".$members->count().' members)');
            $created++;
        }

        $appCount = $this->backfillApplications($dry);
        $created += $appCount;

        $totalProfil = ProfilAsnaf::count();
        $totalApp = Application::count();

        $this->newLine();
        $this->info("Backfill complete: {$created} created, {$skipped} skipped.");
        $this->line("  profil_asnafs: <info>{$totalProfil}</info> | applications: <info>{$totalApp}</info>");
        $this->line('  households now: <info>'.Household::count().'</info>');

        if ($dry) {
            $this->warn('Dry run — run without --dry-run to apply changes.');
        }

        return self::SUCCESS;
    }

    private function groupProfilAsnafs()
    {
        $all = ProfilAsnaf::all();

        if ($all->isEmpty()) {
            return collect();
        }

        $groups = [];
        foreach ($all as $p) {
            $key = ! empty($p->ic_ketua_rumah) ? $p->ic_ketua_rumah : $p->ic;
            $groups[$key][] = $p;
        }

        return collect($groups)->map(fn ($g) => collect($g));
    }

    private function mapHubungan(ProfilAsnaf $p, string $ketuaIC): string
    {
        if ($p->ic === $ketuaIC) {
            return 'Ketua Isi Rumah';
        }

        if ($p->hubungan) {
            $lower = strtolower($p->hubungan);
            $map = [
                'isteri' => 'Isteri',
                'suami' => 'Suami',
                'anak' => 'Anak',
                'ibu' => 'Ibu',
                'bapa' => 'Bapa',
                'ibu bapa' => 'Ibu Bapa',
            ];
            foreach ($map as $key => $label) {
                if (str_contains($lower, $key)) {
                    return $label;
                }
            }
        }

        return 'Tanggungan';
    }

    private function calcAge(string $ic): int
    {
        if (strlen($ic) < 6) {
            return 0;
        }
        $yy = (int) substr($ic, 0, 2);
        $mm = (int) substr($ic, 2, 2);
        $dd = (int) substr($ic, 4, 2);
        $year = $yy > 30 ? 1900 + $yy : 2000 + $yy;
        try {
            return Carbon::createFromDate($year, $mm, $dd)->age;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function guessPendidikan(ProfilAsnaf $p): ?string
    {
        $age = $this->calcAge($p->ic);
        if ($p->n_ipt > 0 || $p->hubungan === 'Anak' && $age >= 18 && $age <= 25) {
            return 'IPT';
        }
        if ($age >= 13 && $age <= 17) {
            return 'Sekolah Menengah';
        }
        if ($age >= 7 && $age <= 12) {
            return 'Sekolah Rendah';
        }
        if ($age >= 4 && $age <= 6) {
            return 'Tadika';
        }

        return null;
    }

    private function backfillApplications(bool $dry): int
    {
        $created = 0;
        $apps = Application::whereIn('status', ['submitted', 'under_review', 'approved'])->get();

        foreach ($apps as $app) {
            $existing = HouseholdMember::withTrashed()->where('ic', $app->ic_no)->first();
            if ($existing) {
                continue;
            }

            if ($dry) {
                $this->line("  <fg=green>+</> Would create household from application: <info>{$app->full_name}</info> ({$app->ic_no})");
                $created++;

                continue;
            }

            $household = Household::create([
                'alamat' => $app->address,
                'daerah' => $app->district,
                'total_income' => $app->total_household_income ?? $app->monthly_income ?? 0,
                'total_expenses' => 0,
                'ai_score' => $app->ai_score ?? 0,
                'eligibility_status' => 'Dalam Semakan',
                'status' => 'active',
            ]);

            HouseholdMember::create([
                'household_id' => $household->id,
                'nama' => $app->full_name,
                'ic' => $app->ic_no,
                'hubungan' => 'Ketua Isi Rumah',
                'umur' => $this->calcAge($app->ic_no),
                'pekerjaan' => $app->occupation,
                'pendidikan' => null,
                'pendapatan_bulanan' => $app->monthly_income ?? 0,
                'status_kesihatan' => null,
                'applicant_status' => 'active',
                'assistance_status' => $app->status === 'approved' ? 'Menerima Bantuan' : null,
            ]);

            if (! empty($app->dependents)) {
                $deps = is_array($app->dependents) ? $app->dependents : json_decode($app->dependents, true);
                if (is_array($deps)) {
                    foreach ($deps as $dep) {
                        if (! empty($dep['ic'])) {
                            HouseholdMember::create([
                                'household_id' => $household->id,
                                'nama' => $dep['name'] ?? 'Tanggungan',
                                'ic' => $dep['ic'] ?? ('DEP-'.uniqid()),
                                'hubungan' => $dep['relationship'] ?? 'Tanggungan',
                                'umur' => (int) ($dep['age'] ?? 0),
                                'pekerjaan' => null,
                                'pendidikan' => null,
                                'pendapatan_bulanan' => (float) ($dep['income'] ?? 0),
                                'status_kesihatan' => null,
                                'applicant_status' => 'active',
                                'assistance_status' => null,
                            ]);
                        }
                    }
                }
            }

            (new HouseholdAiService)->calculateHouseholdScore($household);
            $this->line("  <fg=green>✓</> <info>{$household->household_id}</info> — {$app->full_name} (from application)");
            $created++;
        }

        return $created;
    }
}
