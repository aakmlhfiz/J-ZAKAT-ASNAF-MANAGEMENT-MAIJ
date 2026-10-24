<?php

namespace App\Console\Commands;

use App\Models\ProfilAsnaf;
use App\Services\HadAlKifayahService;
use Illuminate\Console\Command;

class SyncApprovalStatusCommand extends Command
{
    protected $signature = 'profiles:sync-approval-status';

    protected $description = 'Backfill status_kelayakan for profiles whose approval_status is out of sync';

    public function handle(): int
    {
        $approved = ProfilAsnaf::query()
            ->where('approval_status', 'approved')
            ->where('status_kelayakan', '!=', HadAlKifayahService::STATUS_LAYAK)
            ->update(['status_kelayakan' => HadAlKifayahService::STATUS_LAYAK]);

        $rejected = ProfilAsnaf::query()
            ->where('approval_status', 'rejected')
            ->where('status_kelayakan', '!=', HadAlKifayahService::STATUS_TIDAK_LAYAK)
            ->update(['status_kelayakan' => HadAlKifayahService::STATUS_TIDAK_LAYAK]);

        $this->info("{$approved} profil diluluskan diselaraskan ke 'Layak'.");
        $this->info("{$rejected} profil ditolak diselaraskan ke 'Tidak Layak'.");

        return self::SUCCESS;
    }
}
