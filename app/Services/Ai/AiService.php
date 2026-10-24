<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\MockProvider;
use App\Services\Ai\Providers\OllamaProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use InvalidArgumentException;

/**
 * Dispatcher: picks the active AiProvider based on config('ai.default'),
 * and trims/normalizes the input before forwarding.
 */
class AiService
{
    private AiProvider $provider;

    public function __construct()
    {
        $default = config('ai.default', 'mock');
        $this->provider = match ($default) {
            'mock' => new MockProvider,
            'openai' => new OpenAiProvider,
            'gemini' => new GeminiProvider,
            'ollama' => new OllamaProvider,
            default => throw new InvalidArgumentException("Unknown AI provider: {$default}"),
        };
    }

    public function provider(): AiProvider
    {
        return $this->provider;
    }

    public function name(): string
    {
        return $this->provider->name();
    }

    public function chat(string $message, array $history = []): array
    {
        $message = trim($message);
        $maxLen = (int) config('ai.max_message_length', 1000);
        if (mb_strlen($message) > $maxLen) {
            $message = mb_substr($message, 0, $maxLen);
        }

        $maxHistory = (int) config('ai.max_history', 20);
        if (count($history) > $maxHistory) {
            $history = array_slice($history, -$maxHistory);
        }

        $result = $this->provider->chat($message, $history);

        // Normalize: every response must have a 'reply' string.
        if (! isset($result['reply']) || ! is_string($result['reply'])) {
            $result['reply'] = '';
        }
        $result['provider'] = $this->provider->name();
        $result['data'] = $result['data'] ?? ['type' => 'text'];

        return $result;
    }
}
