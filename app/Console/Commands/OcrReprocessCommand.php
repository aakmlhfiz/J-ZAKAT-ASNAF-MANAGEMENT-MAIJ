<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDocumentImport;
use App\Models\DocumentImport;
use Illuminate\Console\Command;

class OcrReprocessCommand extends Command
{
    protected $signature = 'ocr:reprocess {id?} {--all-stuck}';

    protected $description = 'Reprocess a stuck/failed DocumentImport. Use {id} or --all-stuck to retry every non-committed import.';

    public function handle(): int
    {
        set_time_limit(600);

        if ($this->option('all-stuck')) {
            $imports = DocumentImport::whereIn('status', ['processing', 'failed'])->get();
        } elseif ($id = $this->argument('id')) {
            $imports = DocumentImport::where('id', $id)->whereIn('status', ['processing', 'failed'])->get();
        } else {
            $this->error('Sila berikan {id} atau gunakan --all-stuck.');

            return self::FAILURE;
        }

        if ($imports->isEmpty()) {
            $this->info('Tiada import perlu diproses semula.');

            return self::SUCCESS;
        }

        foreach ($imports as $import) {
            $import->update([
                'status' => 'processing',
                'error_message' => null,
                'ocr_started_at' => now(),
                'ocr_completed_at' => null,
            ]);

            $this->line("Memproses semula #{$import->id} — {$import->original_filename} ...");

            try {
                ProcessDocumentImport::dispatchSync($import->id);
                $import->refresh();
                $this->line("  → status: <info>{$import->status}</info> (confidence: {$import->overall_confidence})");
            } catch (\Throwable $e) {
                $this->error("  → gagal: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
