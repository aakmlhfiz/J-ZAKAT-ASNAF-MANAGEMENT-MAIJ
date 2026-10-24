<?php

namespace App\Services\Ocr\Contracts;

interface TextExtractor
{
    /**
     * Extract raw text from a file.
     *
     * @param  string  $filePath  Absolute path to the file
     * @return string Raw extracted text
     */
    public function extract(string $filePath): string;

    /**
     * Unique identifier for this extractor.
     */
    public function name(): string;
}
