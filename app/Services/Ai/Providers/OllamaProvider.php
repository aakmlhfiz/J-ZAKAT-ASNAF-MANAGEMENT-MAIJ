<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;

/**
 * Stub for Ollama (local LLM, e.g. llama3.1). To enable:
 *   1. Install ollama + pull a model:  ollama pull llama3.1
 *   2. .env:  AI_PROVIDER=ollama  AI_OLLAMA_HOST=http://127.0.0.1:11434
 *   3. Implement POST to {host}/api/chat with the 5 tool definitions.
 *   Ollama supports OpenAI-compatible tool calling since v0.3.0.
 */
class OllamaProvider implements AiProvider
{
    public function chat(string $message, array $history = []): array
    {
        $host = config('ai.providers.ollama.host');

        // Cheap reachability check so the stub fails fast and informatively.
        try {
            $resp = Http::timeout(2)->get(rtrim($host, '/').'/api/tags');
            if ($resp->failed()) {
                return [
                    'reply' => "Ollama tidak dapat dihubungi di {$host}. Pastikan servis ollama berjalan dan AI_OLLAMA_HOST betul.",
                    'data' => ['type' => 'text', 'stub' => true, 'reachable' => false],
                ];
            }
        } catch (\Throwable $e) {
            return [
                'reply' => "Ollama tidak dapat dihubungi di {$host} ({$e->getMessage()}).",
                'data' => ['type' => 'text', 'stub' => true, 'reachable' => false],
            ];
        }

        return [
            'reply' => "[Ollama stub] Ollama is reachable. Would POST to {$host}/api/chat here with the 5 tool definitions. See TODO in OllamaProvider.",
            'data' => ['type' => 'text', 'stub' => true, 'reachable' => true],
        ];
    }

    public function name(): string
    {
        return 'ollama';
    }
}
