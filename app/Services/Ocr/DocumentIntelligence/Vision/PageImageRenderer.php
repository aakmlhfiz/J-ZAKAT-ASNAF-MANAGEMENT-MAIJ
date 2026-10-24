<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision;

use Throwable;

/**
 * Renders every page of a PDF or image document to a base64-encoded PNG for the
 * vision model. No OCR is performed — this is a pure rasterisation step.
 */
class PageImageRenderer
{
    private int $dpi;

    private int $maxPages;

    public function __construct(?int $dpi = null, ?int $maxPages = null)
    {
        $this->dpi = $dpi ?? (int) config('ocr.vision.render_dpi', 150);
        $this->maxPages = $maxPages ?? (int) config('ocr.vision.max_pages', 4);
    }

    /**
     * @return array<int, array{page:int, image_b64:string, mime:string, width:int, height:int}>
     */
    public function render(string $filePath, string $mimeType): array
    {
        if (! class_exists(\Imagick::class)) {
            return [];
        }

        $isPdf = $mimeType === 'application/pdf';
        $isImage = str_starts_with($mimeType, 'image/');

        if (! $isPdf && ! $isImage) {
            return [];
        }

        try {
            $imagick = new \Imagick;
            if ($isPdf) {
                $imagick->setResolution($this->dpi, $this->dpi);
            }
            $imagick->readImage($filePath);

            $pageCount = min($imagick->getNumberImages(), $this->maxPages);
            $pages = [];

            for ($i = 0; $i < $pageCount; $i++) {
                $imagick->setIteratorIndex($i);
                $page = $imagick->getImage();
                $page->setImageFormat('png');

                $width = $page->getImageWidth();
                $height = $page->getImageHeight();
                $blob = $page->getImageBlob();

                $pages[] = [
                    'page' => $i + 1,
                    'image_b64' => base64_encode($blob),
                    'mime' => 'image/png',
                    'width' => $width,
                    'height' => $height,
                ];

                $page->destroy();
            }

            $imagick->destroy();

            return $pages;
        } catch (Throwable $e) {
            logger()->warning('Vision page render failed', [
                'file' => basename($filePath),
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
