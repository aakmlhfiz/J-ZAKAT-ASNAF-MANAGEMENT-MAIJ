<?php

namespace App\Console\Commands;

use App\Models\DocumentImport;
use Illuminate\Console\Command;

class PruneStaleImportsCommand extends Command
{
    protected $signature = 'imports:prune-stale
                            {--dry-run : Only report what would be changed}
                            {--older-than=60 : Mark as failed when stuck in processing for more than this many minutes}';

    protected $description = 'Mark DocumentImport records stuck in "processing" for too long as failed';

    public function handle(): int
    {
        $minutes = (int) $this->option('older-than');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subMinutes($minutes);

        $query = DocumentImport::where('status', 'processing')
            ->where('created_at', '<', $cutoff);

        $count = $query->count();

        $this->info("Found {$count} import(s) stuck in 'processing' for more than {$minutes} minute(s).");

        if ($count === 0) {
            return self::SUCCESS;
        }

        $ids = $query->pluck('id')->all();

        if ($dryRun) {
            $this->table(['id', 'filename', 'created_at'], collect($ids)->map(function ($id) {
                $import = DocumentImport::find($id);

                return [$import->id, $import->original_filename, $import->created_at->toDateTimeString()];
            }));

            $this->warn('Dry run — nothing changed. Re-run without --dry-run to mark them failed.');

            return self::SUCCESS;
        }

        DocumentImport::whereIn('id', $ids)->update([
            'status' => 'failed',
            'error_message' => 'Pemprosesan terhenti kerana melebihi had masa. Sila muat naik semula dokumen ini.',
            'ocr_completed_at' => now(),
            'progress' => 0,
        ]);

        $this->info('Marked '.count($ids).' import(s) as failed.');

        return self::SUCCESS;
    }
}