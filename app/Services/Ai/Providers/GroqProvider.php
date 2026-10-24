<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\ToolRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Groq provider using its OpenAI-compatible chat completions endpoint.
 * Supports function calling against the local J-ZAKAT tool registry.
 */
class GroqProvider implements AiProvider
{
    private const MAX_TOOL_ROUNDS = 4;

    private ToolRegistry $tools;

    public function __construct(?ToolRegistry $tools = null)
    {
        $this->tools = $tools ?? new ToolRegistry;
    }

    public function chat(string $message, array $history = []): array
    {
        $apiKey = trim((string) config('ai.providers.groq.api_key'));

        if ($apiKey === '') {
            return [
                'reply' => 'Groq belum dikonfigurasi. Tetapkan AI_GROQ_API_KEY dalam .env dan AI_PROVIDER=groq.',
                'data' => ['type' => 'text', 'error' => 'not_configured'],
            ];
        }

        $base = rtrim((string) config('ai.providers.groq.base', 'https://api.groq.com/openai/v1'), '/');
        $model = (string) config('ai.providers.groq.model', 'llama-3.3-70b-versatile');
        $timeout = max(5, (int) config('ai.providers.groq.timeout', 30));
        $maxTokens = max(200, (int) config('ai.providers.groq.max_tokens', 900));

        $messages = $this->messages($message, $history);
        $tools = $this->tools->definitions();
        $lastToolResult = null;

        for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
            try {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout($timeout)
                    ->post($base.'/chat/completions', [
                        'model' => $model,
                        'messages' => $messages,
                        'tools' => $tools,
                        'tool_choice' => 'auto',
                        'temperature' => 0.2,
                        'max_tokens' => $maxTokens,
                    ]);
            } catch (Throwable $e) {
                Log::error('Groq request failed', ['error' => $e->getMessage()]);

                return $this->error('Perkhidmatan AI Groq tidak dapat dihubungi. Sila cuba lagi sebentar.');
            }

            if ($response->failed()) {
                Log::error('Groq API returned an error', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return $this->error('Perkhidmatan AI Groq sedang menghadapi masalah. Sila cuba lagi sebentar.');
            }

            $payload = $response->json();
            $choice = data_get($payload, 'choices.0.message', []);
            $toolCalls = is_array($choice) ? ($choice['tool_calls'] ?? []) : [];

            if (empty($toolCalls)) {
                $reply = trim((string) data_get($choice, 'content', ''));

                if ($reply === '') {
                    $reply = 'Maaf, saya tidak dapat menjana jawapan buat masa ini.';
                }

                return [
                    'reply' => $reply,
                    'data' => $lastToolResult['data'] ?? ['type' => 'text'],
                ];
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => data_get($choice, 'content'),
                'tool_calls' => $toolCalls,
            ];

            foreach ($toolCalls as $call) {
                $name = (string) data_get($call, 'function.name', '');
                $arguments = json_decode((string) data_get($call, 'function.arguments', '{}'), true);

                if (! is_array($arguments)) {
                    $arguments = [];
                }

                try {
                    $result = $this->tools->execute($name, $arguments);
                } catch (Throwable $e) {
                    Log::error('Groq tool execution failed', [
                        'tool' => $name,
                        'error' => $e->getMessage(),
                    ]);

                    $result = [
                        'reply' => 'Maaf, alat data tidak dapat dilaksanakan buat masa ini.',
                        'data' => ['type' => 'text', 'error' => 'tool'],
                    ];
                }

                $lastToolResult = $result;

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => (string) data_get($call, 'id', ''),
                    'name' => $name,
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];
            }
        }

        if ($lastToolResult) {
            return [
                'reply' => $lastToolResult['reply'],
                'data' => $lastToolResult['data'] ?? ['type' => 'text'],
            ];
        }

        return $this->error('Saya tidak dapat menyelesaikan permintaan ini. Sila cuba soalan yang lebih spesifik.');
    }

    public function name(): string
    {
        return 'groq';
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function messages(string $message, array $history): array
    {
        $messages = [[
            'role' => 'system',
            'content' => (string) config('ai.system_prompt'),
        ]];

        foreach ($history as $item) {
            $role = $item['role'] ?? null;
            $content = $item['content'] ?? null;

            if (! in_array($role, ['user', 'assistant'], true) || ! is_string($content)) {
                continue;
            }

            $content = trim($content);

            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }

    /**
     * @return array{reply: string, data: array<string, mixed>}
     */
    private function error(string $message): array
    {
        return [
            'reply' => $message,
            'data' => ['type' => 'text', 'error' => 'provider'],
        ];
    }
}
