<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision;

use App\Services\Ocr\DocumentIntelligence\Vision\Contracts\VisionProvider;
use App\Services\Ocr\DocumentIntelligence\Vision\Providers\GeminiVisionProvider;
use App\Services\Ocr\DocumentIntelligence\Vision\Providers\MockVisionProvider;
use App\Services\Ocr\DocumentIntelligence\Vision\Providers\OllamaVisionProvider;
use App\Services\Ocr\DocumentIntelligence\Vision\Providers\OpenAiVisionProvider;
use InvalidArgumentException;

class VisionProviderFactory
{
    public static function resolve(?string $name = null, array $mockFixture = []): VisionProvider
    {
        $name = $name ?? (string) config('ocr.vision.provider', 'openai');

        return match ($name) {
            'mock' => new MockVisionProvider($mockFixture),
            'openai' => new OpenAiVisionProvider,
            'gemini' => new GeminiVisionProvider,
            'ollama' => new OllamaVisionProvider,
            default => throw new InvalidArgumentException("Unknown vision provider: {$name}"),
        };
    }
}
