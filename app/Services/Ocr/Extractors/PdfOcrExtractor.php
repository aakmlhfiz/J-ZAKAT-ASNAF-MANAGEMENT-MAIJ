<?php

namespace App\Services\Ocr\Extractors;

use App\Services\Ocr\Contracts\TextExtractor;

class PdfOcrExtractor implements TextExtractor
{
    private TesseractExtractor $tesseract;

    private int $maxPages;

    private int $dpi;

    private bool $pageMarkers;

    public function __construct(?array $config = null)
    {
        $this->tesseract = new TesseractExtractor($config);
        $this->maxPages = $config['pdf_to_image']['max_pages'] ?? config('ocr.pdf_to_image.max_pages', 5);
        $this->dpi = (int) ($config['pdf_to_image']['dpi'] ?? config('ocr.pdf_to_image.dpi', 220));
        $this->pageMarkers = (bool) config('ocr.page_markers.enabled', true);
    }

    public function extract(string $filePath): string
    {
        $mime = mime_content_type($filePath);

        if ($mime !== 'application/pdf') {
            throw new \InvalidArgumentException("PdfOcrExtractor requires a PDF file, got: {$mime}");
        }

        $texts = [];

        try {
            $imagick = new \Imagick;
            $imagick->setResolution($this->dpi, $this->dpi);
            $imagick->readImage($filePath);

            $pageCount = min($imagick->getNumberImages(), $this->maxPages);

            for ($i = 0; $i < $pageCount; $i++) {
                $imagick->setIteratorIndex($i);
                $imagick->setImageFormat('png');

                $tmpFile = tempnam(sys_get_temp_dir(), 'ocr_pdf_').'.png';
                $imagick->writeImage($tmpFile);

                try {
                    $pageText = $this->tesseract->extract($tmpFile);
                    if (trim($pageText) !== '') {
                        $texts[] = ($this->pageMarkers ? '--- PAGE '.($i + 1)." ---\n" : '').$pageText;
                    }
                } finally {
                    @unlink($tmpFile);
                }
            }

            $imagick->destroy();

        } catch (\Throwable $e) {
            throw new \RuntimeException("PDF-to-image OCR failed: {$e->getMessage()}", 0, $e);
        }

        return trim(implode("\n\n", $texts));
    }

    public function name(): string
    {
        return 'pdf-ocr';
    }
}
