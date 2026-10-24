<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision\Providers;

use App\Services\Ocr\DocumentIntelligence\Vision\Contracts\VisionProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Ollama local vision provider (e.g. qwen2.5vl / llava). Runs free on the
 * server via POST {host}/api/generate with base64 images.
 */
class OllamaVisionProvider implements VisionProvider
{
    public function understand(array $pages, array $prompt): array
    {
        $host = rtrim((string) config('ocr.vision.ollama.host', 'http://127.0.0.1:11434'), '/');
        $model = (string) config('ocr.vision.ollama.model', 'qwen2.5vl:7b');

        try {
            $response = Http::timeout((int) config('ocr.vision.timeout', 120))
                ->connectTimeout(10)
                ->post($host.'/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt['system']."\n\n".$prompt['user'],
                    'images' => array_map(fn ($page) => $page['image_b64'], $pages),
                    'stream' => false,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                logger()->error('Ollama vision request failed', [
                    'status' => $response->status(),
                    'body' => substr((string) $response->body(), 0, 500),
                ]);

                return [];
            }

            $text = $response->json('response');
            if (! is_string($text) || trim($text) === '') {
                return [];
            }

            $decoded = json_decode($text, true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            logger()->error('Ollama vision exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function name(): string
    {
        return 'ollama';
    }

    public static function isConfigured(): bool
    {
        return true;
    }

    public function isLocal(): bool
    {
        return true;
    }
}
