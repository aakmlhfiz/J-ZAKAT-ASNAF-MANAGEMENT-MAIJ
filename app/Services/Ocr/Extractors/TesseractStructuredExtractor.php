<?php

namespace App\Services\Ocr\Extractors;

use App\Services\Ocr\Contracts\ProvidesWordConfidence;
use App\Services\Ocr\Contracts\TextExtractor;
use App\Services\Ocr\ImagePreprocessor;
use RuntimeException;
use Throwable;

/**
 * One-pass Tesseract OCR for supporting documents.
 *
 * Tesseract TSV gives us the extracted words, line grouping and real
 * word-level confidence in one run. This replaces the previous approach of
 * running Tesseract once for text and a second time for confidence.
 */
class TesseractStructuredExtractor implements TextExtractor, ProvidesWordConfidence
{
    private string $tesseractPath;

    private string $language;

    private int $psm;

    private int $oem;

    private int $dpi;

    private int $maxPages;

    private int $maxDimension;

    private ImagePreprocessor $preprocessor;

    /** @var array<int, array{page:int, width:int, height:int, words:array<int, array{text:string, conf:float}>}> */
    private array $wordPages = [];

    /** @var array<string, int> */
    private array $timings = [];

    private bool $truncated = false;

    public function __construct(?array $tesseractConfig = null, ?ImagePreprocessor $preprocessor = null)
    {
        $tesseractConfig ??= config('ocr.tesseract', []);
        $generic = config('document_ocr.ocr', []);

        $this->tesseractPath = (string) ($tesseractConfig['path'] ?? 'tesseract');
        $this->language = (string) ($tesseractConfig['language'] ?? 'eng+msa');
        $this->psm = (int) ($generic['psm'] ?? 6);
        $this->oem = (int) ($generic['oem'] ?? 1);
        $this->dpi = (int) ($generic['dpi'] ?? 200);
        $this->maxPages = max(1, (int) ($generic['max_pages'] ?? 10));
        $this->maxDimension = max(800, (int) ($generic['max_dimension'] ?? 2400));
        $this->preprocessor = $preprocessor ?? new ImagePreprocessor;
    }

    public function extract(string $filePath): string
    {
        if (! TesseractExtractor::isAvailable()) {
            throw new RuntimeException('Tesseract binary is not available.');
        }

        $mime = mime_content_type($filePath);
        if (! str_starts_with($mime, 'image/') && $mime !== 'application/pdf') {
            throw new \InvalidArgumentException("TesseractStructuredExtractor requires an image or PDF, got: {$mime}");
        }

        $this->wordPages = [];
        $this->timings = ['render_ms' => 0, 'ocr_ms' => 0];
        $this->truncated = false;
        $renderStarted = microtime(true);
        $pageImages = $this->renderPages($filePath, $mime);
        $this->timings['render_ms'] = (int) round((microtime(true) - $renderStarted) * 1000);

        $pageTexts = [];
        $ocrStarted = microtime(true);

        try {
            foreach ($pageImages as $page) {
                [$text, $words] = $this->ocrPage($page['path'], $page['page']);
                if (trim($text) !== '') {
                    $pageTexts[$page['page']] = $text;
                }
                if ($words !== []) {
                    $this->wordPages[] = [
                        'page' => $page['page'],
                        'width' => $page['width'],
                        'height' => $page['height'],
                        'words' => $words,
                    ];
                }
            }
        } finally {
            foreach ($pageImages as $page) {
                if ($page['temp'] && is_file($page['path'])) {
                    @unlink($page['path']);
                }
            }
        }

        $this->timings['ocr_ms'] = (int) round((microtime(true) - $ocrStarted) * 1000);

        if ($pageTexts === []) {
            return '';
        }

        ksort($pageTexts);
        if (count($pageTexts) === 1) {
            return trim(reset($pageTexts));
        }

        $parts = [];
        foreach ($pageTexts as $page => $text) {
            $parts[] = '--- PAGE '.$page." ---\n".$text;
        }

        return trim(implode("\n\n", $parts));
    }

    public function wordPages(): array
    {
        return $this->wordPages;
    }

    public function timings(): array
    {
        return $this->timings;
    }

    public function truncated(): bool
    {
        return $this->truncated;
    }

    public function maxPages(): int
    {
        return $this->maxPages;
    }

    public function name(): string
    {
        return 'tesseract-structured';
    }

    /**
     * @return array<int, array{page:int, path:string, width:int, height:int, temp:bool}>
     */
    private function renderPages(string $filePath, string $mime): array
    {
        if (str_starts_with($mime, 'image/')) {
            $processed = $this->preprocessor->process($filePath);
            if ($processed !== $filePath) {
                return [['page' => 1, 'path' => $processed, 'width' => 0, 'height' => 0, 'temp' => true]];
            }

            return [['page' => 1, 'path' => $filePath, 'width' => 0, 'height' => 0, 'temp' => false]];
        }

        if (! class_exists(\Imagick::class)) {
            throw new RuntimeException('Imagick is required to OCR scanned PDF pages.');
        }

        $images = [];
        $imagick = new \Imagick;
        $imagick->setResolution($this->dpi, $this->dpi);
        $imagick->readImage($filePath);
        $totalPages = $imagick->getNumberImages();
        if ($totalPages > $this->maxPages) {
            $this->truncated = true;
        }
        $pageCount = min($totalPages, $this->maxPages);

        try {
            for ($i = 0; $i < $pageCount; $i++) {
                $imagick->setIteratorIndex($i);
                $page = $imagick->getImage();
                $width = $page->getImageWidth();
                $height = $page->getImageHeight();

                if ($width > $this->maxDimension) {
                    $scale = $this->maxDimension / $width;
                    $page->resizeImage(
                        (int) round($width * $scale),
                        (int) round($height * $scale),
                        \Imagick::FILTER_LANCZOS,
                        1
                    );
                    $width = $page->getImageWidth();
                    $height = $page->getImageHeight();
                }

                $page->setImageFormat('png');
                $tmpBase = tempnam(sys_get_temp_dir(), 'doc_ocr_');
                if ($tmpBase === false) {
                    $page->destroy();

                    continue;
                }
                $tmp = $tmpBase.'.png';
                @unlink($tmpBase);
                $page->writeImage($tmp);
                $page->destroy();

                $images[] = [
                    'page' => $i + 1,
                    'path' => $tmp,
                    'width' => $width,
                    'height' => $height,
                    'temp' => true,
                ];
            }
        } finally {
            $imagick->clear();
            $imagick->destroy();
        }

        return $images;
    }

    /**
     * @return array{0:string, 1:array<int, array{text:string, conf:float}>}
     */
    private function ocrPage(string $imagePath, int $page): array
    {
        $base = tempnam(sys_get_temp_dir(), 'doc_ocr_');
        if ($base === false) {
            throw new RuntimeException('Unable to allocate a temporary OCR file.');
        }
        @unlink($base);

        $output = [];
        $code = 0;
        $cmd = sprintf(
            '"%s" "%s" "%s" --oem %d --psm %d -l %s tsv 2>&1',
            $this->tesseractPath,
            $imagePath,
            $base,
            $this->oem,
            $this->psm,
            $this->language
        );
        exec($cmd, $output, $code);
        $tsv = $base.'.tsv';

        if ($code !== 0 || ! is_file($tsv)) {
            @unlink($base);
            @unlink($tsv);

            throw new RuntimeException('Tesseract TSV extraction failed.');
        }

        $lines = [];
        $words = [];
        $currentKey = null;
        $currentWords = [];

        try {
            $handle = fopen($tsv, 'rb');
            if ($handle === false) {
                throw new RuntimeException('Unable to read Tesseract TSV output.');
            }

            fgetcsv($handle, 0, "\t"); // header
            while (($columns = fgetcsv($handle, 0, "\t")) !== false) {
                if ((int) ($columns[0] ?? -1) !== 5) {
                    continue;
                }
                $word = trim((string) ($columns[11] ?? ''));
                if ($word === '') {
                    continue;
                }

                $confidence = (float) ($columns[10] ?? 0);
                if ($confidence < 0) {
                    continue;
                }

                $lineKey = implode('-', [
                    $columns[1] ?? 0,
                    $columns[2] ?? 0,
                    $columns[3] ?? 0,
                    $columns[4] ?? 0,
                ]);

                if ($currentKey !== null && $lineKey !== $currentKey && $currentWords !== []) {
                    $lines[] = implode(' ', array_column($currentWords, 'text'));
                    $currentWords = [];
                }

                $currentKey = $lineKey;
                $currentWords[] = ['text' => $word, 'conf' => $confidence];
                $words[] = ['text' => $word, 'conf' => $confidence];
            }

            if ($currentWords !== []) {
                $lines[] = implode(' ', array_column($currentWords, 'text'));
            }

            fclose($handle);
        } finally {
            @unlink($tsv);
        }

        return [trim(implode("\n", $lines)), $words];
    }
}
