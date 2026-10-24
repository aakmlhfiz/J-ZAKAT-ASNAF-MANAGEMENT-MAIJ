<?php

namespace App\Services\Ocr;

use Smalot\PdfParser\Parser;

class PdfPageAnalyzer
{
    public function analyze(string $filePath, string $mimeType): array
    {
        $result = [
            'page_count' => 1,
            'has_text_layer' => false,
            'is_scanned' => false,
            'pages' => [],
        ];

        if ($mimeType !== 'application/pdf') {
            return $result;
        }

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();

            $result['page_count'] = count($pages);
            $totalTextLength = 0;

            foreach ($pages as $index => $page) {
                $text = $page->getText();
                $length = mb_strlen(trim($text));
                $totalTextLength += $length;

                $result['pages'][] = [
                    'page_number' => $index + 1,
                    'text_length' => $length,
                    'has_content' => $length > 50,
                ];
            }

            $result['has_text_layer'] = $totalTextLength > 100;
            $result['is_scanned'] = $totalTextLength < 100;

        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
