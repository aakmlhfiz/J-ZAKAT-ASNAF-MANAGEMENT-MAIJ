<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ServeDoctorCommand extends Command
{
    protected $signature = 'serve:doctor {--fix : Kill all detected serve instances} {--port=8000 : Port to inspect}';

    protected $description = 'Diagnose duplicate `php artisan serve` instances on a port and optionally kill them';

    public function handle(): int
    {
        $port = (int) $this->option('port');
        $fix = (bool) $this->option('fix');

        $this->info("=== Serve Doctor: port {$port} ===");
        $this->newLine();

        $parents = $this->serveParents();
        $listeners = $this->portListeners($port);
        $workers = $this->innerWorkers($port);

        $this->line("artisan serve parents : <info>{$parents}</info>");
        $this->line("php -S inner workers  : <info>{$workers}</info>");
        $this->line("port {$port} listeners  : <info>{$listeners}</info>");
        $this->newLine();

        if ($parents <= 1 && $listeners <= 1) {
            $this->info('Single instance - all good.');

            return self::SUCCESS;
        }

        $this->warn('Multiple serve processes detected - this causes intermittent upload failures.');
        $this->newLine();

        if (! $fix) {
            $this->line('Run with --fix to kill all serve instances, then restart with:');
            $this->line('  php artisan serve --host=127.0.0.1 --port='.$port.' --no-reload');

            return self::SUCCESS;
        }

        $this->info('Killing all serve processes...');
        $killed = $this->killAllServe($port);
        $this->line("  Killed {$killed} process(es).");
        $this->newLine();
        $this->line('Port '.$port.' is now free. Restart with:');
        $this->line('  php artisan serve --host=127.0.0.1 --port='.$port.' --no-reload');

        return self::SUCCESS;
    }

    private function serveParents(): int
    {
        $cmd = DIRECTORY_SEPARATOR === '\\'
            ? "wmic process where \"name like '%php%' and commandline like '%artisan serve%'\" get processid 2>&1"
            : "pgrep -fc 'artisan serve' 2>/dev/null";

        $output = [];
        exec($cmd, $output);

        return $this->countNumericLines($output);
    }

    private function innerWorkers(int $port): int
    {
        $cmd = DIRECTORY_SEPARATOR === '\\'
            ? "wmic process where \"name like '%php%' and commandline like '%server.php%' and commandline like '%:{$port}%'\" get processid 2>&1"
            : "pgrep -fc 'server\.php' 2>/dev/null";

        $output = [];
        exec($cmd, $output);

        return $this->countNumericLines($output);
    }

    private function portListeners(int $port): int
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $output = [];
            exec('netstat -ano 2>&1', $output);
            $listeners = array_filter($output, fn ($line) =>
                str_contains($line, ":{$port}") && str_contains($line, 'LISTENING')
            );

            return count($listeners);
        }

        $output = [];
        exec("ss -ltn 2>/dev/null | grep :{$port} | wc -l", $output);
        $count = (int) trim($output[0] ?? '');

        return $count > 0 ? $count : 0;
    }

    private function countNumericLines(array $output): int
    {
        $count = 0;
        foreach ($output as $line) {
            $line = trim($line);
            // Exclude the current command's own process (e.g. `serve:doctor`).
            if (ctype_digit($line) && (int) $line !== getmypid()) {
                $count++;
            }
        }

        return $count;
    }

    private function killAllServe(int $port): int
    {
        $targets = [];

        if (DIRECTORY_SEPARATOR === '\\') {
            // Collect PIDs from wmic for serve parents + inner workers, and netstat listeners.
            $queries = [
                "wmic process where \"name like '%php%' and commandline like '%artisan serve%'\" get processid 2>&1",
                "wmic process where \"name like '%php%' and commandline like '%server.php%' and commandline like '%:{$port}%'\" get processid 2>&1",
            ];

            foreach ($queries as $q) {
                $out = [];
                exec($q, $out);
                foreach ($out as $line) {
                    $line = trim($line);
                    if (ctype_digit($line) && (int) $line !== getmypid()) {
                        $targets[] = (int) $line;
                    }
                }
            }

            // cmd.exe shims wrapping the inner php -S server.
            $shims = [];
            exec("wmic process where \"name like '%cmd%' and commandline like '%server.php%' and commandline like '%:{$port}%'\" get processid 2>&1", $shims);
            foreach ($shims as $line) {
                $line = trim($line);
                if (ctype_digit($line) && (int) $line !== getmypid()) {
                    $targets[] = (int) $line;
                }
            }

            // netstat listener PIDs.
            $ns = [];
            exec('netstat -ano 2>&1', $ns);
            foreach ($ns as $line) {
                if (str_contains($line, ":{$port}") && str_contains($line, 'LISTENING')) {
                    $parts = preg_split('/\s+/', trim($line));
                    $pid = (int) end($parts);
                    if ($pid > 0) {
                        $targets[] = $pid;
                    }
                }
            }
        } else {
            $out = [];
            exec("pgrep -f 'artisan serve' 2>/dev/null", $out);
            foreach ($out as $line) {
                $line = trim($line);
                if (ctype_digit($line) && (int) $line !== getmypid()) {
                    $targets[] = (int) $line;
                }
            }
        }

        $targets = array_values(array_unique($targets));
        $killed = 0;

        foreach ($targets as $pid) {
            if ($pid === getmypid() || $pid <= 0) {
                continue;
            }
            if (DIRECTORY_SEPARATOR === '\\') {
                exec('taskkill /PID '.$pid.' /T /F 2>&1');
            } else {
                exec('kill -9 '.$pid.' 2>&1');
            }
            $killed++;
        }

        return $killed;
    }
}
