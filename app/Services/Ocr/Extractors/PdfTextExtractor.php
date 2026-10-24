<?php

namespace App\Services\Ocr\Extractors;

use App\Services\Ocr\Contracts\TextExtractor;
use InvalidArgumentException;
use Smalot\PdfParser\Parser;

class PdfTextExtractor implements TextExtractor
{
    private bool $pageMarkers;

    public function __construct(?bool $pageMarkers = null)
    {
        $this->pageMarkers = $pageMarkers ?? (bool) config('ocr.page_markers.enabled', true);
    }

    public function extract(string $filePath): string
    {
        $mime = mime_content_type($filePath);

        if ($mime !== 'application/pdf') {
            throw new InvalidArgumentException("PdfTextExtractor requires a PDF file, got: {$mime}");
        }

        try {
            $parser = new Parser;
            $document = $parser->parseFile($filePath);

            if (! $this->pageMarkers) {
                return trim($document->getText());
            }

            $pages = [];
            foreach ($document->getPages() as $index => $page) {
                $pageText = trim($page->getText());
                if ($pageText !== '') {
                    $pages[] = '--- PAGE '.($index + 1)." ---\n".$pageText;
                }
            }

            return trim(implode("\n\n", $pages));
        } catch (\Throwable $e) {
            throw new \RuntimeException("PDF text extraction failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function name(): string
    {
        return 'pdfparser';
    }
}
