<?php

namespace Tests\Feature;

use App\Services\Ocr\DocumentIntelligence\Vision\Providers\GeminiVisionProvider;
use App\Services\Ocr\DocumentIntelligence\Vision\Providers\OllamaVisionProvider;
use App\Services\Ocr\DocumentIntelligence\Vision\Providers\OpenAiVisionProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisionProvidersHttpTest extends TestCase
{
    private function pages(): array
    {
        return [
            ['page' => 1, 'image_b64' => base64_encode('png-bytes-1'), 'mime' => 'image/png', 'width' => 1000, 'height' => 1400],
            ['page' => 2, 'image_b64' => base64_encode('png-bytes-2'), 'mime' => 'image/png', 'width' => 1000, 'height' => 1400],
        ];
    }

    private function prompt(): array
    {
        return ['system' => 'system-prompt', 'user' => 'user-prompt'];
    }

    public function test_openai_sends_image_urls_and_parses_json(): void
    {
        config(['ocr.vision.openai.api_key' => 'sk-test']);
        config(['ocr.vision.openai.base' => 'https://api.openai.com/v1']);
        config(['ocr.vision.openai.model' => 'gpt-4o-mini']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => '{"applicant":{"nama":{"value":"Ali","confidence":95}}}']]],
            ], 200),
        ]);

        $provider = new OpenAiVisionProvider;
        $this->assertTrue(OpenAiVisionProvider::isConfigured());

        $result = $provider->understand($this->pages(), $this->prompt());

        $this->assertSame('Ali', $result['applicant']['nama']['value']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $content = $body['messages'][1]['content'];

            return str_contains($request->url(), '/chat/completions')
                && ($body['response_format']['type'] ?? null) === 'json_object'
                && $content[0]['type'] === 'text'
                && $content[1]['type'] === 'image_url'
                && str_contains($content[1]['image_url']['url'], 'data:image/png;base64,cG5nLWJ5dGVzLTE=');
        });
    }

    public function test_openai_not_configured_without_key(): void
    {
        config(['ocr.vision.openai.api_key' => '']);

        $this->assertFalse(OpenAiVisionProvider::isConfigured());
    }

    public function test_gemini_sends_inline_data_and_parses_json(): void
    {
        config(['ocr.vision.gemini.api_key' => 'gemini-key']);
        config(['ocr.vision.gemini.model' => 'gemini-2.0-flash']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{"applicant":{"nama":{"value":"Siti","confidence":90}}}']]]]],
            ], 200),
        ]);

        $provider = new GeminiVisionProvider;
        $result = $provider->understand($this->pages(), $this->prompt());

        $this->assertSame('Siti', $result['applicant']['nama']['value']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $parts = $body['contents'][0]['parts'];

            return str_contains($request->url(), ':generateContent')
                && ($body['generationConfig']['responseMimeType'] ?? null) === 'application/json'
                && $parts[0]['text'] === 'user-prompt'
                && $parts[1]['inlineData']['data'] === base64_encode('png-bytes-1');
        });
    }

    public function test_ollama_sends_images_and_parses_json(): void
    {
        config(['ocr.vision.ollama.host' => 'http://127.0.0.1:11434']);
        config(['ocr.vision.ollama.model' => 'qwen2.5vl:7b']);

        Http::fake([
            '127.0.0.1:11434/*' => Http::response([
                'response' => '{"applicant":{"nama":{"value":"Ali","confidence":95}}}',
            ], 200),
        ]);

        $provider = new OllamaVisionProvider;
        $result = $provider->understand($this->pages(), $this->prompt());

        $this->assertSame('Ali', $result['applicant']['nama']['value']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($request->url(), '/api/generate')
                && ($body['format'] ?? null) === 'json'
                && ($body['images'][0] ?? null) === base64_encode('png-bytes-1')
                && ($body['stream'] ?? null) === false;
        });
    }

    public function test_ollama_is_local_and_configured(): void
    {
        $provider = new OllamaVisionProvider;

        $this->assertTrue(OllamaVisionProvider::isConfigured());
        $this->assertTrue($provider->isLocal());
    }
}
