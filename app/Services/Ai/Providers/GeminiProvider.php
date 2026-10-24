<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;

/**
 * Stub for Google Gemini. To enable:
 *   1. .env:  AI_PROVIDER=gemini  AI_GEMINI_API_KEY=...
 *   2. Implement POST to https://generativelanguage.googleapis.com/...
 *      with function declarations for the 5 local tools.
 */
class GeminiProvider implements AiProvider
{
    public function chat(string $message, array $history = []): array
    {
        $apiKey = config('ai.providers.gemini.api_key');

        if (! $apiKey) {
            return [
                'reply' => 'Gemini provider is not configured. Set AI_GEMINI_API_KEY in .env and AI_PROVIDER=gemini to enable.',
                'data' => ['type' => 'text', 'stub' => true],
            ];
        }

        return [
            'reply' => '[Gemini stub] Would call Gemini here with the system prompt and 5 function declarations. See TODO in GeminiProvider.',
            'data' => ['type' => 'text', 'stub' => true],
        ];
    }

    public function name(): string
    {
        return 'gemini';
    }
}
