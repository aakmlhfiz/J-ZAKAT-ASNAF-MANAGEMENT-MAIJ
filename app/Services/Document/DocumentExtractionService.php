<?php

namespace App\Services\Document;

use App\Enums\DocumentType;
use App\Models\DocumentImport;
use App\Services\Document\Extractors\DocumentExtractor;
use App\Services\Ocr\Contracts\TextExtractor;
use App\Services\Ocr\DocumentIntelligence\TesseractWordExtractor;
use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Support\Facades\Storage;

class DocumentExtractionService
{
    /**
     * Extract structured fields for a generic supporting document.
     *
     * @return array{ocr_result:array<string,mixed>, fields:array<string,mixed>, overall_confidence:float|null, warnings:array<int,string>}
     */
    public function extract(
        DocumentImport $import,
        string $text,
        array $pageInfo,
        TextExtractor $extractor,
    ): array {
        $type = DocumentType::fromStored($import->document_type);
        $class = $type->extractorClass();
        /** @var DocumentExtractor $documentExtractor */
        $documentExtractor = new $class;

        $wordPages = $this->confidenceWordPages($import, $pageInfo, $extractor);
        $resolver = new DocumentConfidenceResolver($wordPages);

        $context = [
            'confidence_resolver' => $resolver,
            'mime_type' => $import->mime_type,
            'page_info' => $pageInfo,
            'ocr_engine' => $extractor->name(),
        ];

        $result = $documentExtractor->extract($text, $context);
        $schema = DocumentFieldSchema::fields($type);
        $fields = [];

        foreach ($schema as $key => $definition) {
            $fields[$key] = $result['fields'][$key] ?? $this->emptyField($key, $definition);
        }

        // Preserve extractor-only fields that are not in the schema (rare).
        foreach ($result['fields'] as $key => $field) {
            if (! isset($fields[$key])) {
                $fields[$key] = $field;
            }
        }

        $warnings = $result['warnings'] ?? [];
        $requiredMissing = [];
        foreach ($schema as $key => $definition) {
            if (($definition['required'] ?? false) && ($fields[$key]['value'] ?? null) === null) {
                $requiredMissing[] = $definition['label'];
            }
        }
        if ($requiredMissing !== []) {
            $warnings[] = __('document_ocr.warnings.required_missing', ['fields' => implode(', ', $requiredMissing)]);
        }

        $confidences = array_values(array_filter(
            array_map(fn ($field) => $field['confidence'] ?? null, $fields),
            fn ($confidence) => $confidence !== null,
        ));
        $overallConfidence = $confidences === [] ? null : round(array_sum($confidences) / count($confidences), 1);

        $confidenceMap = [];
        foreach ($fields as $key => $field) {
            $confidenceMap[$key] = $field['confidence'] ?? null;
        }

        $meta = array_merge($result['meta'] ?? [], [
            'page_count' => (int) ($pageInfo['page_count'] ?? $import->page_count ?? 1),
            'has_text_layer' => (bool) ($pageInfo['has_text_layer'] ?? false),
            'confidence_available' => $resolver->available(),
            'ocr_engine' => $extractor->name(),
        ]);

        $ocrResult = [
            'document_type' => $type->value,
            'status' => 'awaiting_verification',
            'provider' => $extractor->name(),
            'extracted_data' => $fields,
            'confidence' => $confidenceMap,
            'warnings' => array_values(array_unique($warnings)),
            'meta' => $meta,
        ];

        return [
            'ocr_result' => $ocrResult,
            'fields' => $fields,
            'overall_confidence' => $overallConfidence,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    /**
     * Only run the extra Tesseract TSV pass when it can produce real
     * confidence. Text-layer PDFs and non-Tesseract engines return null.
     */
    private function confidenceWordPages(DocumentImport $import, array $pageInfo, TextExtractor $extractor): array
    {
        if (! (bool) config('document_ocr.confidence_enabled', true)) {
            return [];
        }

        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            return [];
        }

        $mime = (string) $import->mime_type;
        if (! str_starts_with($mime, 'image/') && $mime !== 'application/pdf') {
            return [];
        }

        // A text-layer PDF has no raster OCR confidence to report.
        if ($mime === 'application/pdf' && ($pageInfo['has_text_layer'] ?? false) && $extractor->name() === 'pdfparser') {
            return [];
        }

        try {
            $path = Storage::disk(config('ocr.disk', 'local'))->path($import->stored_path);
            if (! file_exists($path)) {
                return [];
            }

            return (new TesseractWordExtractor)->extractPages($path, $mime);
        } catch (\Throwable $e) {
            logger()->warning('Generic document confidence extraction failed', [
                'document_import_id' => $import->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function emptyField(string $key, array $definition): array
    {
        return [
            'value' => null,
            'original_value' => null,
            'normalized_value' => null,
            'confidence' => null,
            'confidence_source' => null,
            'confidence_page' => null,
            'needs_review' => true,
            'page' => null,
            'source_label' => null,
            'warnings' => [],
            'field_type' => $definition['type'] ?? 'text',
        ];
    }
}
