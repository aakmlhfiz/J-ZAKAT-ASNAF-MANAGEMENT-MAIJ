<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision\Providers;

use App\Services\Ocr\DocumentIntelligence\Vision\Contracts\VisionProvider;

/**
 * Offline provider that returns a canned structured result. Used by tests and
 * as a no-cost "smoke" provider. Always configured.
 */
class MockVisionProvider implements VisionProvider
{
    public function __construct(private array $fixture = []) {}

    public function understand(array $pages, array $prompt): array
    {
        if (! empty($this->fixture)) {
            return $this->fixture;
        }

        return [
            'document' => [
                'pages' => count($pages),
                'document_type' => 'MAIJ Zakat Assistance Application Form',
                'sections_detected' => [],
            ],
            'applicant' => [],
            'applications' => [],
            'fields' => [],
            'checkboxes' => [],
        ];
    }

    public function name(): string
    {
        return 'mock';
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
