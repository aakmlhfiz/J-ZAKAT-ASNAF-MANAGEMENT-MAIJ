<?php

namespace App\Services\Document;

use App\Models\DocumentImport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Throwable;

class DocumentPreviewService
{
    public function pageCount(DocumentImport $import): int
    {
        if (str_starts_with((string) $import->mime_type, 'image/')) {
            return 1;
        }

        if ($import->mime_type !== 'application/pdf') {
            return 1;
        }

        if ((int) ($import->page_count ?? 0) > 0) {
            return (int) $import->page_count;
        }

        try {
            $path = $this->sourcePath($import);
            if (! $path || ! file_exists($path)) {
                return 1;
            }

            return max(1, count((new Parser)->parseFile($path)->getPages()));
        } catch (Throwable) {
            return 1;
        }
    }

    /**
     * Return a local path to a page image. PDF pages are rendered once into
     * private storage and reused. Images return the original private file.
     */
    public function pagePath(DocumentImport $import, int $page = 1): ?string
    {
        $page = max(1, $page);
        $source = $this->sourcePath($import);
        if (! $source || ! file_exists($source)) {
            return null;
        }

        if (str_starts_with((string) $import->mime_type, 'image/')) {
            return $page === 1 ? $source : null;
        }

        if ($import->mime_type !== 'application/pdf') {
            return null;
        }

        if ($page > $this->pageCount($import)) {
            return null;
        }

        $cacheRoot = Storage::disk(config('ocr.disk', 'local'))->path(
            (string) config('document_ocr.preview.cache_path', 'ocr/previews')
        );
        $target = $cacheRoot.DIRECTORY_SEPARATOR.$import->id.DIRECTORY_SEPARATOR.'page-'.$page.'.png';

        if (is_file($target) && filesize($target) > 0) {
            return $target;
        }

        if (! class_exists(\Imagick::class)) {
            return null;
        }

        try {
            File::ensureDirectoryExists(dirname($target));

            $dpi = (int) config('document_ocr.preview.dpi', 150);
            $imagick = new \Imagick;
            $imagick->setResolution($dpi, $dpi);
            $imagick->readImage($source);
            $imagick->setIteratorIndex($page - 1);
            $imagick->setImageFormat('png');
            $imagick->writeImage($target);
            $imagick->clear();
            $imagick->destroy();

            return is_file($target) ? $target : null;
        } catch (Throwable $e) {
            logger()->warning('Document preview render failed', [
                'document_import_id' => $import->id,
                'page' => $page,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function clear(DocumentImport $import): void
    {
        $cacheRoot = Storage::disk(config('ocr.disk', 'local'))->path(
            (string) config('document_ocr.preview.cache_path', 'ocr/previews')
        );
        File::deleteDirectory($cacheRoot.DIRECTORY_SEPARATOR.$import->id);
    }

    private function sourcePath(DocumentImport $import): ?string
    {
        if (! $import->stored_path) {
            return null;
        }

        return Storage::disk(config('ocr.disk', 'local'))->path($import->stored_path);
    }
}
