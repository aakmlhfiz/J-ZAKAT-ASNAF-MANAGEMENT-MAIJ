<?php

namespace App\Services\Ocr\DocumentIntelligence;

use RuntimeException;
use Throwable;

/**
 * Renders each page of a document to an image (Imagick) and runs Tesseract in
 * TSV mode to obtain word-level bounding boxes and per-word confidence. Also
 * provides an ink-density probe used by {@see CheckboxDetector} and signature
 * detection. All coordinates are reported in the rendered page's native pixels;
 * {@see BorangFormUnderstanding} normalises them into the template reference
 * space via {@see WordGrid}.
 */
class TesseractWordExtractor
{
    private string $tesseractPath;

    private string $language;

    private int $dpi;

    private bool $available;

    /** @var array<int, array{page:int, width:int, height:int, imagick:\Imagick|null}> */
    private array $pages = [];

    public function __construct()
    {
        $this->tesseractPath = (string) config('ocr.tesseract.path', 'tesseract');
        $this->language = (string) config('ocr.tesseract.language', 'eng+msa');
        $this->dpi = (int) config('ocr.document_intelligence.word_dpi', 220);
        $this->available = class_exists(\Imagick::class);
    }

    public function available(): bool
    {
        return $this->available;
    }

    /**
     * Render and OCR every page. Returns per-page word arrays.
     *
     * @return array<int, array{page:int, width:int, height:int, words:array<int, array{l:int,t:int,w:int,h:int,conf:float,text:string}>}>
     */
    public function extractPages(string $filePath, string $mimeType): array
    {
        if (! $this->available) {
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

            if (! $isPdf && $isImage) {
                $imagick->setImageFormat('png');
            }

            $pageCount = $imagick->getNumberImages();
            $result = [];

            for ($i = 0; $i < $pageCount; $i++) {
                $imagick->setIteratorIndex($i);
                $page = $imagick->getImage();
                $page->setImageFormat('png');

                $width = $page->getImageWidth();
                $height = $page->getImageHeight();

                $tmpFile = tempnam(sys_get_temp_dir(), 'maij_ocr_').'.png';
                $page->writeImage($tmpFile);

                $words = $this->ocrWords($tmpFile);
                @unlink($tmpFile);

                $this->pages[$i] = [
                    'page' => $i + 1,
                    'width' => $width,
                    'height' => $height,
                    'imagick' => $page,
                ];

                $result[] = [
                    'page' => $i + 1,
                    'width' => $width,
                    'height' => $height,
                    'words' => $words,
                ];
            }

            $imagick->destroy();

            return $result;
        } catch (Throwable $e) {
            logger()->warning('TesseractWordExtractor render failed', [
                'file' => basename($filePath),
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Measure the dark-pixel ratio of a rectangle in the given rendered page.
     * Coordinates are native page pixels. Returns null when unavailable.
     */
    public function density(int $pageIndex, int $x, int $y, int $w, int $h): ?float
    {
        $page = $this->pages[$pageIndex - 1] ?? null;
        if (! $page || ! $page['imagick'] instanceof \Imagick) {
            return null;
        }

        try {
            $crop = clone $page['imagick'];
            $crop->cropImage(max(1, $w), max(1, $h), $x, $y);
            $crop->setImageColorspace(\Imagick::COLORSPACE_GRAY);

            $dark = 0;
            $total = 0;
            $it = $crop->getPixelIterator();
            foreach ($it as $row) {
                foreach ($row as $px) {
                    $total++;
                    if ($px->getColorValue(\Imagick::COLOR_RED) < 0.45) {
                        $dark++;
                    }
                }
            }
            $crop->destroy();

            return $total > 0 ? $dark / $total : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function destroy(): void
    {
        foreach ($this->pages as $page) {
            if ($page['imagick'] instanceof \Imagick) {
                $page['imagick']->destroy();
            }
        }
        $this->pages = [];
    }

    /**
     * @return array<int, array{l:int,t:int,w:int,h:int,conf:float,text:string}>
     */
    private function ocrWords(string $pngPath): array
    {
        $base = tempnam(sys_get_temp_dir(), 'maij_ocr_');
        $cmd = '"'.$this->tesseractPath.'" "'.$pngPath.'" "'.$base.'" --psm 6 -l '.$this->language.' tsv 2>&1';

        $output = [];
        $code = 0;
        exec($cmd, $output, $code);

        $tsvFile = $base.'.tsv';
        if (! file_exists($tsvFile)) {
            @unlink($base);

            throw new RuntimeException('Tesseract did not produce TSV output.');
        }

        $words = [];
        $lines = file($tsvFile);
        foreach (array_slice($lines, 1) as $line) {
            $c = str_getcsv(trim($line), "\t");
            if ((int) ($c[0] ?? -1) === 5 && trim((string) ($c[11] ?? '')) !== '') {
                $words[] = [
                    'l' => (int) $c[6],
                    't' => (int) $c[7],
                    'w' => (int) $c[8],
                    'h' => (int) $c[9],
                    'conf' => (float) $c[10],
                    'text' => trim($c[11]),
                ];
            }
        }

        @unlink($base);
        @unlink($tsvFile);

        return $words;
    }
}
