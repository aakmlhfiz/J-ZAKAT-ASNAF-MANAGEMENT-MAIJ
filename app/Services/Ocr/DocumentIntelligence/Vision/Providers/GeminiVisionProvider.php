<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision\Providers;

use App\Services\Ocr\DocumentIntelligence\Vision\Contracts\VisionProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Google Gemini vision provider (generateContent). Each page is sent as an
 * inline_data PNG; `responseMimeType: application/json` forces a JSON reply.
 */
class GeminiVisionProvider implements VisionProvider
{
    public function understand(array $pages, array $prompt): array
    {
        $config = config('ocr.vision.gemini', []);
        $apiKey = (string) ($config['api_key'] ?? '');
        $model = (string) ($config['model'] ?? 'gemini-2.0-flash');

        $parts = array_map(fn ($page) => [
            'inlineData' => ['mimeType' => $page['mime'], 'data' => $page['image_b64']],
        ], $pages);
        array_unshift($parts, ['text' => $prompt['user']]);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout((int) config('ocr.vision.timeout', 120))
                ->connectTimeout(30)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'systemInstruction' => ['parts' => [['text' => $prompt['system']]]],
                        'contents' => [['role' => 'user', 'parts' => $parts]],
                        'generationConfig' => [
                            'temperature' => 0,
                            'responseMimeType' => 'application/json',
                        ],
                    ]
                );

            if ($response->failed()) {
                logger()->error('Gemini vision request failed', [
                    'status' => $response->status(),
                    'body' => substr((string) $response->body(), 0, 500),
                ]);

                return [];
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            if (! is_string($text) || trim($text) === '') {
                return [];
            }

            $decoded = json_decode($text, true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            logger()->error('Gemini vision exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function name(): string
    {
        return 'gemini';
    }

    public static function isConfigured(): bool
    {
        return ! empty(config('ocr.vision.gemini.api_key', ''));
    }

    public function isLocal(): bool
    {
        return false;
    }
}
