<?php

namespace App\Services\Ocr\Extractors;

use App\Services\Ocr\Contracts\TextExtractor;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Smalot\PdfParser\Parser;

class MistralOcrExtractor implements TextExtractor
{
    private string $apiKey;

    private string $baseUrl;

    private string $model;

    private int $timeout;

    private int $maxPages;

    private int $retryAttempts;

    private int $retryBackoffMs;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('ocr.mistral', []);
        $this->apiKey = (string) ($config['api_key'] ?? config('ocr.mistral.api_key', ''));
        $this->baseUrl = rtrim($config['base_url'] ?? config('ocr.mistral.base_url', 'https://api.mistral.ai'), '/');
        $this->model = $config['model'] ?? config('ocr.mistral.model', 'mistral-ocr-latest');
        $this->timeout = (int) ($config['timeout'] ?? config('ocr.mistral.timeout', 60));
        $this->maxPages = (int) ($config['max_pages'] ?? config('ocr.mistral.max_pages', 10));
        $this->retryAttempts = (int) ($config['retry_attempts'] ?? config('ocr.mistral.retry_attempts', 2));
        $this->retryBackoffMs = (int) ($config['retry_backoff_ms'] ?? config('ocr.mistral.retry_backoff_ms', 1000));
    }

    public function extract(string $filePath): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Mistral OCR API key is not configured. Set MISTRAL_API_KEY in .env (or use the hybrid driver).');
        }

        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $mime = mime_content_type($filePath);
        $isPdf = ($mime === 'application/pdf');
        $isImage = str_starts_with($mime, 'image/');

        if (! $isPdf && ! $isImage) {
            throw new InvalidArgumentException("MistralOcrExtractor requires a PDF or image file, got: {$mime}");
        }

        if ($isPdf) {
            $this->assertPageCountWithinLimit($filePath);
        }

        $document = $this->buildDocumentPayload($filePath);

        $response = $this->sendWithRetry($document);

        if ($response->failed()) {
            $detail = $response->json('detail') ?? $response->body();
            if (is_array($detail)) {
                $detail = json_encode($detail);
            }

            throw new RuntimeException('Mistral OCR API error (HTTP '.$response->status().'): '.substr((string) $detail, 0, 500));
        }

        if (($response->json('status') ?? null) === 'needs_processing') {
            throw new RuntimeException('Mistral OCR memerlukan pemprosesan tidak segerak (needs_processing). Dokumen terlalu besar untuk pemprosesan segera.');
        }

        $pages = $response->json('pages') ?? [];

        if (empty($pages)) {
            throw new RuntimeException('Mistral OCR returned no pages for the document.');
        }

        return $this->flattenPages($pages);
    }

    private function assertPageCountWithinLimit(string $filePath): void
    {
        if ($this->maxPages <= 0) {
            return;
        }

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($filePath);
            $pageCount = count($pdf->getPages());
        } catch (\Throwable $e) {
            return;
        }

        if ($pageCount > $this->maxPages) {
            throw new RuntimeException(
                "Mistral OCR: dokumen mempunyai {$pageCount} muka surat melebihi had {$this->maxPages}. Sila pecahkan dokumen kepada fail yang lebih kecil."
            );
        }
    }

    /**
     * POST the OCR request, retrying transient failures (429 / 5xx) with a
     * short backoff.
     */
    private function sendWithRetry(array $document)
    {
        $attempt = 0;

        do {
            $attempt++;

            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout($this->timeout)
                    ->connectTimeout($this->timeout)
                    ->acceptJson()
                    ->asJson()
                    ->post($this->baseUrl.'/v1/ocr', [
                        'model' => $this->model,
                        'document' => $document,
                    ]);
            } catch (\Throwable $e) {
                if ($attempt > $this->retryAttempts) {
                    throw new RuntimeException('Mistral OCR request failed: '.$e->getMessage(), 0, $e);
                }

                usleep($this->retryBackoffMs * 1000 * $attempt);

                continue;
            }

            if (! $response->failed() || ! in_array($response->status(), [408, 425, 429, 500, 502, 503, 504], true)) {
                return $response;
            }

            if ($attempt <= $this->retryAttempts) {
                usleep($this->retryBackoffMs * 1000 * $attempt);
            }
        } while ($attempt <= $this->retryAttempts);

        return $response;
    }

    /**
     * Build the Mistral OCR `document` payload. For images we send base64
     * directly; for PDFs we send base64 as well (page count is capped by
     * {@see assertPageCountWithinLimit}).
     */
    private function buildDocumentPayload(string $filePath): array
    {
        $base64 = base64_encode(file_get_contents($filePath));

        return [
            'type' => 'document_url',
            'document_base64' => $base64,
            'document_name' => basename($filePath),
        ];
    }

    /**
     * Concatenate the markdown text from every returned page.
     */
    private function flattenPages(array $pages): string
    {
        $text = [];

        foreach ($pages as $page) {
            if (! is_array($page)) {
                continue;
            }

            $markdown = $page['markdown'] ?? '';
            if (is_string($markdown) && trim($markdown) !== '') {
                $text[] = trim($markdown);
            }
        }

        return trim(implode("\n\n", $text));
    }

    public function name(): string
    {
        return 'mistral';
    }

    public static function isConfigured(): bool
    {
        return ! empty(config('ocr.mistral.api_key', ''));
    }
}
