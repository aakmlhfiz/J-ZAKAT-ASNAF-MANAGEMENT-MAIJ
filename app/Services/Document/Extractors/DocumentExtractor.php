<?php

namespace App\Services\Document\Extractors;

interface DocumentExtractor
{
    /**
     * @param  array<string, mixed>  $context
     * @return array{fields:array<string, array<string, mixed>>, warnings:array<int, string>, meta:array<string, mixed>}
     */
    public function extract(string $text, array $context): array;
}
