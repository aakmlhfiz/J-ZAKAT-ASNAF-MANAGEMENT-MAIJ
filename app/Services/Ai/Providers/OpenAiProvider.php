<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;

/**
 * Stub for OpenAI (ChatGPT). To enable:
 *   1. composer require openai-php/laravel
 *   2. .env:  AI_PROVIDER=openai  AI_OPENAI_API_KEY=sk-...
 *
 * When implemented, this provider should:
 *   - POST https://api.openai.com/v1/chat/completions
 *   - Define the 5 tools as OpenAI "functions" / "tools" for function-calling
 *   - When OpenAI returns a tool_calls response, execute the matching local
 *     AbstractTool and feed the result back to get the final natural-language
 *     reply.
 *   - Return { reply, data } in the same shape as MockProvider.
 */
class OpenAiProvider implements AiProvider
{
    public function chat(string $message, array $history = []): array
    {
        $apiKey = config('ai.providers.openai.api_key');

        if (! $apiKey) {
            return [
                'reply' => 'OpenAI provider is not configured. Set AI_OPENAI_API_KEY in .env and AI_PROVIDER=openai to enable real LLM responses.',
                'data' => ['type' => 'text', 'stub' => true],
            ];
        }

        // TODO: implement real OpenAI function-calling flow.
        return [
            'reply' => '[OpenAI stub] Would call ChatGPT here with the system prompt and 5 tool definitions. See TODO in OpenAiProvider.',
            'data' => ['type' => 'text', 'stub' => true],
        ];
    }

    public function name(): string
    {
        return 'openai';
    }
}
