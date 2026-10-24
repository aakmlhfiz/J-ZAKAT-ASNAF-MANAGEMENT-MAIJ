<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision\Contracts;

/**
 * A vision-capable model that reads rendered page images and returns structured
 * JSON describing the document (field label → value, checkboxes, tables,
 * signatures, confidence).
 */
interface VisionProvider
{
    /**
     * Send the rendered page images to the model and return decoded structured
     * JSON. Must not throw on a model failure — return [] instead so the caller
     * can fall back to the local pipeline.
     *
     * @param  array<int, array{page:int, image_b64:string, mime:string, width:int, height:int}>  $pages
     * @param  array{system:string, user:string}  $prompt
     * @return array<mixed>
     */
    public function understand(array $pages, array $prompt): array;

    /** Unique identifier matching config('ocr.vision.provider'). */
    public function name(): string;

    /** Whether the provider is usable with the current configuration. */
    public static function isConfigured(): bool;

    /** Whether the provider runs locally (no API key / per-page cost). */
    public function isLocal(): bool;
}
