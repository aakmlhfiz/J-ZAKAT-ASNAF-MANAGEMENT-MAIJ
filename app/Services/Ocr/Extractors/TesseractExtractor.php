<?php

namespace App\Services\Ocr\Extractors;

use App\Services\Ocr\Contracts\TextExtractor;
use App\Services\Ocr\ImagePreprocessor;
use InvalidArgumentException;
use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractExtractor implements TextExtractor
{
    private string $tesseractPath;

    private string $language;

    private int $psm;

    private int $oem;

    private ImagePreprocessor $preprocessor;

    public function __construct(?array $config = null, ?ImagePreprocessor $preprocessor = null)
    {
        $config = $config ?? config('ocr.tesseract', []);
        $this->tesseractPath = $config['path'] ?? 'tesseract';
        $this->language = $config['language'] ?? 'eng+msa';
        $this->psm = $config['psm'] ?? 3;
        $this->oem = $config['oem'] ?? 3;
        $this->preprocessor = $preprocessor ?? new ImagePreprocessor;
    }

    public function extract(string $filePath): string
    {
        $mime = mime_content_type($filePath);

        if (! str_starts_with($mime, 'image/') && $mime !== 'application/pdf') {
            throw new InvalidArgumentException("TesseractExtractor requires an image or PDF file, got: {$mime}");
        }

        $processedPath = $filePath;

        try {
            if (str_starts_with($mime, 'image/')) {
                $processedPath = $this->preprocessor->process($filePath);
            }

            $ocr = new TesseractOCR($processedPath);

            if ($this->tesseractPath && $this->tesseractPath !== 'tesseract') {
                $ocr->executable($this->tesseractPath);
            }

            $ocr->lang($this->language);
            $ocr->psm($this->psm);
            $ocr->oem($this->oem);

            return trim($ocr->run());
        } catch (\Throwable $e) {
            throw new \RuntimeException("Tesseract OCR extraction failed: {$e->getMessage()}", 0, $e);
        } finally {
            $this->preprocessor->cleanup($processedPath, $filePath);
        }
    }

    public function name(): string
    {
        return 'tesseract';
    }

    /**
     * Check if tesseract binary is available.
     */
    public static function isAvailable(): bool
    {
        $path = config('ocr.tesseract.path', 'tesseract');

        $output = [];
        $code = 0;

        exec(sprintf('"%s" --version 2>&1', $path), $output, $code);

        return $code === 0;
    }

    /**
     * Get tesseract version string.
     */
    public static function version(): string
    {
        $path = config('ocr.tesseract.path', 'tesseract');

        $output = [];
        $code = 0;

        exec(sprintf('"%s" --version 2>&1', $path), $output, $code);

        return $code === 0 ? trim(implode(' ', $output)) : '(not found)';
    }
}
