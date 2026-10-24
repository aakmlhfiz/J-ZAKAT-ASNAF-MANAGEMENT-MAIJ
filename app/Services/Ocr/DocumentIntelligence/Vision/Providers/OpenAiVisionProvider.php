<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision\Providers;

use App\Services\Ocr\DocumentIntelligence\Vision\Contracts\VisionProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * OpenAI GPT-4o family vision provider (chat completions). One image_url part
 * per page, `response_format: json_object`, temperature 0.
 */
class OpenAiVisionProvider implements VisionProvider
{
    public function understand(array $pages, array $prompt): array
    {
        $config = config('ocr.vision.openai', []);
        $base = rtrim((string) ($config['base'] ?? 'https://api.openai.com/v1'), '/');

        $imageParts = array_map(fn ($page) => [
            'type' => 'image_url',
            'image_url' => [
                'url' => 'data:'.$page['mime'].';base64,'.$page['image_b64'],
                'detail' => 'high',
            ],
        ], $pages);

        try {
            $response = Http::withToken((string) $config['api_key'])
                ->acceptJson()
                ->timeout((int) config('ocr.vision.timeout', 120))
                ->connectTimeout(30)
                ->post($base.'/chat/completions', [
                    'model' => (string) ($config['model'] ?? 'gpt-4o-mini'),
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt['system']],
                        ['role' => 'user', 'content' => array_merge(
                            [['type' => 'text', 'text' => $prompt['user']]],
                            $imageParts
                        )],
                    ],
                ]);

            if ($response->failed()) {
                logger()->error('OpenAI vision request failed', [
                    'status' => $response->status(),
                    'body' => substr((string) $response->body(), 0, 500),
                ]);

                return [];
            }

            $content = $response->json('choices.0.message.content');
            if (! is_string($content) || trim($content) === '') {
                return [];
            }

            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            logger()->error('OpenAI vision exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function name(): string
    {
        return 'openai';
    }

    public static function isConfigured(): bool
    {
        return ! empty(config('ocr.vision.openai.api_key', ''));
    }

    public function isLocal(): bool
    {
        return false;
    }
}
