<?php

namespace App\Console\Commands;

use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;

class OcrCheckCommand extends Command
{
    protected $signature = 'ocr:check';

    protected $description = 'Verify OCR dependencies: Tesseract binary, PDF parser, and configuration';

    public function handle(): int
    {
        $this->info('=== OCR Environment Check ===');
        $this->newLine();

        $this->checkDriver();
        $this->checkTesseract();
        $this->checkPdfParser();
        $this->checkImagick();
        $this->checkOcrMyPdf();
        $this->checkMistral();
        $this->checkVision();
        $this->checkAiRecognizer();
        $this->checkStorage();
        $this->checkQueue();
        $this->checkPhpConfig();
        $this->checkServeInstances();

        $this->newLine();
        $this->info('=== Check Complete ===');

        return self::SUCCESS;
    }

    private function checkDriver(): void
    {
        $driver = config('ocr.default', 'hybrid');
        $this->line("OCR Driver: <info>{$driver}</info>");
    }

    private function checkTesseract(): void
    {
        $path = config('ocr.tesseract.path', 'tesseract');
        $lang = config('ocr.tesseract.language', 'eng+msa');

        $this->line("Tesseract path: <info>{$path}</info>");
        $this->line("Tesseract lang: <info>{$lang}</info>");

        if (TesseractExtractor::isAvailable()) {
            $version = TesseractExtractor::version();
            $this->line('Tesseract: <info>AVAILABLE</info>');
            $this->line("  Version: {$version}");

            $output = [];
            $code = 0;
            exec(sprintf('"%s" --list-langs 2>&1', $path), $output, $code);
            $langs = $code === 0 ? trim(implode("\n", $output)) : '(unavailable)';
            $this->line("  Available languages: <comment>{$langs}</comment>");
        } else {
            $this->line('Tesseract: <error>NOT AVAILABLE</error>');
            $this->line('  Install: <comment>apt install tesseract-ocr tesseract-ocr-msa</comment> (Linux)');
            $this->line('  Or download from: <comment>https://github.com/UB-Mannheim/tesseract/wiki</comment> (Windows)');
        }
        $this->newLine();
    }

    private function checkPdfParser(): void
    {
        if (class_exists(Parser::class)) {
            $this->line('smalot/pdfparser: <info>AVAILABLE</info>');
        } else {
            $this->line('smalot/pdfparser: <error>NOT FOUND</error>');
        }
        $this->newLine();
    }

    private function checkVision(): void
    {
        $enabled = (bool) config('ocr.vision.enabled', false);
        $provider = (string) config('ocr.vision.provider', 'openai');
        $model = (string) config("ocr.vision.{$provider}.model", '—');

        $this->line('Vision AI: <info>'.($enabled ? 'enabled' : 'disabled').'</info> (provider: '.$provider.', model: '.$model.')');

        if (! $enabled) {
            $this->line('  Enable with <comment>OCR_VISION_ENABLED=true</comment> + provider keys (see docs/OCR_SETUP.md)');
            $this->newLine();

            return;
        }

        $configured = match ($provider) {
            'openai' => ! empty(config('ocr.vision.openai.api_key', '')),
            'gemini' => ! empty(config('ocr.vision.gemini.api_key', '')),
            'ollama', 'mock' => true,
            default => false,
        };

        if ($configured) {
            $this->line('  Pipeline: <info>READY</info> — vision document understanding will run for MAIJ forms');
        } else {
            $this->line('  Pipeline: <comment>will fall back to local template OCR</comment> (set the provider API key in .env)');
        }
        $this->newLine();
    }

    private function checkAiRecognizer(): void
    {
        $recognizer = config('ocr.ai_recognizer', 'mock');
        $threshold = config('ocr.confidence_threshold', 70);

        $this->line("AI Recognizer: <info>{$recognizer}</info>");
        $this->line("Confidence Threshold: <info>{$threshold}%</info>");

        $provider = config('ai.default', 'mock');
        $this->line("Current AI Provider: <info>{$provider}</info>");
        $this->newLine();
    }

    private function checkImagick(): void
    {
        if (extension_loaded('imagick')) {
            $this->line('imagick extension: <info>AVAILABLE</info>');
        } else {
            $this->line('imagick extension: <error>NOT AVAILABLE</error>');
            $this->line('  Install: <comment>pecl install imagick</comment>');
            $this->line('  Or download the matching DLL from <comment>https://windows.php.net/downloads/pecl/releases/imagick/</comment>');
            $this->line('  (Scanned PDFs will fall back to Tesseract reading the PDF directly.)');
        }
        $this->newLine();
    }

    private function checkOcrMyPdf(): void
    {
        $enabled = config('ocr.ocr_my_pdf.enabled', false);
        $binary = config('ocr.ocr_my_pdf.binary', 'python');
        $this->line('OCRmyPDF: <info>'.($enabled ? 'ENABLED' : 'DISABLED (opt-in)').'</info> (binary: '.$binary.')');

        $available = false;
        $output = [];
        $code = 0;
        exec(sprintf('"%s" -m ocrmypdf --version 2>&1', $binary), $output, $code);

        if ($code === 0) {
            $available = true;
            $version = trim(implode(' ', $output));
            $this->line('  ocrmypdf: <info>AVAILABLE</info> ('.$version.')');
        } else {
            $this->line('  ocrmypdf: <error>NOT AVAILABLE</error>');
            $this->line('    Install Python 3.10+, QPDF, then: <comment>python -m pip install ocrmypdf</comment>');
        }

        // QPDF check.
        $qpdfOk = false;
        $qpdfPath = null;
        $qout = [];
        exec('where qpdf 2>&1', $qout, $qcode);
        if ($qcode === 0 && ! empty($qout)) {
            $qpdfOk = true;
            $qpdfPath = $qout[0];
        } else {
            // Probe common install locations (PATH may not be refreshed in this process).
            $candidates = [
                getenv('LOCALAPPDATA').'/Programs/QPDF/bin/qpdf.exe',
                'C:/Program Files/QPDF/bin/qpdf.exe',
                'C:/Program Files (x86)/QPDF/bin/qpdf.exe',
            ];
            foreach ($candidates as $c) {
                if (file_exists($c)) {
                    $qpdfOk = true;
                    $qpdfPath = $c;
                    break;
                }
            }
        }

        if ($qpdfOk) {
            $this->line('  qpdf: <info>AVAILABLE</info> ('.$qpdfPath.')');
        } else {
            $this->line('  qpdf: <error>NOT FOUND</error> — download from https://github.com/qpdf/qpdf/releases and add to PATH');
        }

        if ($available && $qpdfOk) {
            $this->line('  OCRmyPDF pipeline: <info>READY</info> (set OCR_MY_PDF_ENABLED=true in .env to use)');
        }
        $this->newLine();
    }

    private function checkMistral(): void
    {
        $driver = config('ocr.default', 'hybrid');
        $apiKey = config('ocr.mistral.api_key', '');
        $hasKey = ! empty($apiKey);
        $model = config('ocr.mistral.model', 'mistral-ocr-latest');

        $this->line('Mistral OCR: <info>'.($driver === 'mistral' ? 'DRIVER ACTIVE' : 'inactive').'</info> (model: '.$model.')');
        $this->line('  API key: '.($hasKey ? '<info>set</info>' : '<error>NOT SET</error>'));

        if ($driver === 'mistral') {
            if ($hasKey) {
                $this->line('  Pipeline: <info>READY</info>');
            } else {
                $this->line('  Pipeline: <comment>will fall back to hybrid</comment> (set MISTRAL_API_KEY in .env to use Mistral)');
            }
        } else {
            $this->line('  Enable with <comment>OCR_DRIVER=mistral</comment> + <comment>MISTRAL_API_KEY=...</comment>');
        }
        $this->newLine();
    }

    private function checkQueue(): void
    {
        $connection = config('queue.default');
        $this->line("Queue connection: <info>{$connection}</info>");

        if ($connection === 'sync') {
            $this->line('  OCR jobs run synchronously inside the HTTP request — uploads may block.');
            $this->line('  For async processing set <comment>QUEUE_CONNECTION=database</comment> and run <comment>php artisan queue:work</comment>.');
        } else {
            $this->line('  Ensure a worker is running: <comment>php artisan queue:work --queue=default --timeout=120</comment>');
        }
        $this->newLine();
    }

    private function checkServeInstances(): void
    {
        $port = 8000;
        $listeners = $this->countPortListeners($port);
        $parents = $this->countServeParents();

        $this->line('Dev server parents (artisan serve): <info>'.$parents.'</info>');
        $this->line('Dev server listeners (port '.$port.'): <info>'.$listeners.'</info>');

        if ($parents > 1 || $listeners > 1) {
            $this->line('Dev server: <error>'.$parents.' parents / '.$listeners.' listeners — serve processes are racing, causing intermittent upload failures. Kill duplicates with <comment>powershell -File scripts/serve-doctor.ps1 -Fix</comment> (or <comment>php artisan serve:doctor --fix</comment>).</error>');
        } elseif ($parents === 1 && $listeners === 1) {
            $this->line('Dev server: <info>single instance</info>');
        } elseif ($parents === 0 && $listeners === 0) {
            $this->line('Dev server: <comment>not running</comment> (start with <comment>php artisan serve --host=127.0.0.1 --port='.$port.' --no-reload</comment>)');
        } else {
            $this->line('Dev server: <comment>'.($parents === 0 ? 'no artisan serve parent' : '1 parent').', '.($listeners === 0 ? 'no listener' : '1 listener').'</comment>');
        }
        $this->newLine();
    }

    private function countPortListeners(int $port): int
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $output = [];
            exec('netstat -ano 2>&1', $output);
            $listeners = array_filter($output, fn ($line) => str_contains($line, ":{$port}") && str_contains($line, 'LISTENING')
            );

            return count($listeners);
        }

        $output = [];
        exec("ss -ltn 2>/dev/null | grep :{$port} | wc -l", $output);
        $count = (int) trim($output[0] ?? '');

        return $count > 0 ? $count : 0;
    }

    private function countServeParents(): int
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $cmd = "wmic process where \"name like '%php%' and commandline like '%artisan serve%'\" get processid 2>&1";
            $output = [];
            exec($cmd, $output);

            $count = 0;
            foreach ($output as $line) {
                if (ctype_digit(trim($line)) && (int) trim($line) !== getmypid()) {
                    $count++;
                }
            }

            return $count;
        }

        $output = [];
        exec("pgrep -fc 'artisan serve' 2>/dev/null", $output);
        $count = (int) trim($output[0] ?? '');

        return $count;
    }

    private function checkStorage(): void
    {
        $disk = config('ocr.disk', 'local');
        $path = config('ocr.path', 'ocr');
        $fullPath = storage_path("app/{$path}");

        if (! is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
            $this->line("Storage path created: <info>{$fullPath}</info>");
        } else {
            $this->line("Storage path: <info>{$fullPath}</info> (exists)");
        }
        $this->line("Storage disk: <info>{$disk}</info>");
        $this->line('Max file size: <info>'.config('ocr.max_file_size_kb', 10240).' KB</info>');
        $this->line('Allowed types: <info>'.implode(', ', config('ocr.allowed_extensions', [])).'</info>');
    }

    private function checkPhpConfig(): void
    {
        $upload = ini_get('upload_max_filesize');
        $post = ini_get('post_max_size');
        $memory = ini_get('memory_limit');
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $maxKb = (int) config('ocr.max_file_size_kb', 10240);

        $uploadBytes = $this->iniSizeToBytes($upload);
        $limitBytes = $maxKb * 1024;

        $uploadOk = $uploadBytes >= $limitBytes;
        $this->line('PHP upload_max_filesize: <info>'.$upload.'</info>'.($uploadOk ? '' : ' <error>(too small for configured limit of '.$maxKb.' KB)</error>'));
        $this->line('PHP post_max_size: <info>'.$post.'</info>');
        $this->line('PHP memory_limit: <info>'.$memory.'</info>');

        if ($isWindows) {
            $this->line('Web server concurrency: <comment>single-threaded</comment> (PHP_CLI_SERVER_WORKERS unsupported on Windows — use XAMPP Apache, Laravel Octane, or IIS for parallel requests)');
        } else {
            $this->line('PHP_CLI_SERVER_WORKERS: <info>'.(env('PHP_CLI_SERVER_WORKERS') ?: 'not set').'</info> (requires `php artisan serve --no-reload`)');
        }
        $this->newLine();
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $num = (int) $value;

        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }
}
