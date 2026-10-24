<?php

namespace App\Services\Ocr\Extractors;

use App\Services\Ocr\Contracts\TextExtractor;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use RuntimeException;

class OcrMyPdfExtractor implements TextExtractor
{
    private string $binary;

    private string $language;

    private array $options;

    private int $timeout;

    private string $workDir;

    private string $tessdataPrefix;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('ocr.ocr_my_pdf', []);
        $this->binary = $config['binary'] ?? config('ocr.ocr_my_pdf.binary', 'python');
        $this->language = $config['language'] ?? config('ocr.ocr_my_pdf.language', 'eng+msa');
        $this->options = $config['options'] ?? config('ocr.ocr_my_pdf.options', ['--deskew', '--optimize', '1']);
        $this->timeout = (int) ($config['timeout'] ?? config('ocr.ocr_my_pdf.timeout', 120));
        $this->workDir = storage_path('app/'.(config('ocr.path', 'ocr')));
        $this->tessdataPrefix = (string) ($config['tessdata_prefix'] ?? config('ocr.ocr_my_pdf.tessdata_prefix', ''));
    }

    public function extract(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $mime = mime_content_type($filePath);
        if ($mime !== 'application/pdf') {
            throw new InvalidArgumentException("OcrMyPdfExtractor requires a PDF file, got: {$mime}");
        }

        // Write the OCR'd PDF to a sibling temp file so the original stays intact.
        $dir = dirname($filePath);
        $outputPath = $dir.DIRECTORY_SEPARATOR.'searchable_'.basename($filePath);

        try {
            $this->runOcrMyPdf($filePath, $outputPath);

            // Read the embedded text layer from the searchable PDF.
            $textExtractor = new PdfTextExtractor;
            $text = $textExtractor->extract($outputPath);

            return trim($text);
        } finally {
            @unlink($outputPath);
        }
    }

    private function runOcrMyPdf(string $input, string $output): void
    {
        $args = [
            $this->binary,
            '-m', 'ocrmypdf',
            '-l', $this->language,
        ];
        foreach ($this->options as $opt) {
            $args[] = $opt;
        }
        $args[] = $input;
        $args[] = $output;

        // Ensure tesseract/qpdf are findable by the subprocess. TESSDATA_PREFIX
        // is only set when explicitly configured (no hardcoded OS path).
        $env = [];
        if ($this->tessdataPrefix !== '') {
            $env['TESSDATA_PREFIX'] = $this->tessdataPrefix;
        }

        $result = Process::path($this->workDir)
            ->timeout($this->timeout)
            ->env($env)
            ->run($args);

        if ($result->failed()) {
            throw new RuntimeException('OCRmyPDF failed (exit '.$result->exitCode().'): '.substr($result->errorOutput(), 0, 500));
        }

        if (! file_exists($output)) {
            throw new RuntimeException('OCRmyPDF reported success but produced no output file.');
        }
    }

    public function name(): string
    {
        return 'ocr-my-pdf';
    }
}
