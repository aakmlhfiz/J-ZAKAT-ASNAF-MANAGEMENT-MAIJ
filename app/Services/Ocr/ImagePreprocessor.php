<?php

namespace App\Services\Ocr;

use RuntimeException;

/**
 * Optional image normalisation before Tesseract OCR.
 *
 * Wraps ImageMagick (Imagick) operations — grayscale, auto-contrast,
 * upscaling of small scans and deskew — to improve accuracy on low-quality
 * source material. All operations are config-gated and degrade gracefully
 * (no-op / passthrough) when Imagick is unavailable or disabled.
 */
class ImagePreprocessor
{
    private bool $enabled;

    private int $minSize;

    private bool $grayscale;

    private bool $contrast;

    private bool $deskew;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('ocr.preprocess', []);
        $this->enabled = (bool) ($config['enabled'] ?? false);
        $this->minSize = (int) ($config['min_size'] ?? 1600);
        $this->grayscale = (bool) ($config['grayscale'] ?? true);
        $this->contrast = (bool) ($config['contrast'] ?? true);
        $this->deskew = (bool) ($config['deskew'] ?? true);
    }

    public static function available(): bool
    {
        return class_exists(\Imagick::class);
    }

    /**
     * Preprocess an image file in place. Returns the path to process (which
     * may differ from the input when a temp copy was created).
     */
    public function process(string $filePath): string
    {
        if (! $this->enabled || ! self::available()) {
            return $filePath;
        }

        if (! file_exists($filePath)) {
            return $filePath;
        }

        $tmpFile = null;

        try {
            $imagick = new \Imagick($filePath);
            $width = $imagick->getImageWidth();

            if ($width > 0 && $width < $this->minSize) {
                $scale = $this->minSize / $width;
                $imagick->resizeImage(
                    (int) round($width * $scale),
                    (int) round($imagick->getImageHeight() * $scale),
                    \Imagick::FILTER_LANCZOS,
                    1
                );
            }

            if ($this->grayscale) {
                $imagick->setImageColorspace(\Imagick::COLORSPACE_GRAY);
                $imagick->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
            }

            if ($this->contrast) {
                $imagick->normalizeImage();
            }

            if ($this->deskew && method_exists($imagick, 'deskewImage')) {
                $imagick->deskewImage(60);
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'ocr_pre_').'.png';
            $imagick->setImageFormat('png');
            $imagick->writeImage($tmpFile);
            $imagick->destroy();

            return $tmpFile;
        } catch (\Throwable $e) {
            if ($tmpFile !== null && file_exists($tmpFile)) {
                @unlink($tmpFile);
            }

            throw new RuntimeException('Image preprocessing failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Clean up any temp file created by {@see process()}.
     */
    public function cleanup(string $filePath, string $originalPath): void
    {
        if ($filePath !== $originalPath && file_exists($filePath)) {
            @unlink($filePath);
        }
    }
}
