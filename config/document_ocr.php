<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Generic supporting-document OCR
    |--------------------------------------------------------------------------
    | The existing MAIJ Borang pipeline is unchanged. These settings only apply
    | to MyKad, salary slips, bank statements, utility bills, rental agreements,
    | medical documents, birth certificates and other supporting documents.
    */
    'confidence_enabled' => (bool) env('DOCUMENT_OCR_CONFIDENCE', true),
    'confidence_source' => 'tesseract_tsv',

    /*
    |--------------------------------------------------------------------------
    | Fast Tesseract settings for supporting documents
    |--------------------------------------------------------------------------
    | One Tesseract TSV pass supplies both text and word confidence. This avoids
    | OCR-ing the same page twice just to calculate confidence.
    */
    'ocr' => [
        'dpi' => (int) env('DOCUMENT_OCR_DPI', 200),
        'psm' => (int) env('DOCUMENT_OCR_PSM', 6),
        'oem' => (int) env('DOCUMENT_OCR_OEM', 1),
        'max_pages' => (int) env('DOCUMENT_OCR_MAX_PAGES', 10),
        'max_dimension' => (int) env('DOCUMENT_OCR_MAX_DIMENSION', 2400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Secure document preview
    |--------------------------------------------------------------------------
    | PDF pages are rendered to private cached images for the review page so
    | applicants/officers can navigate pages without exposing the original file.
    */
    'preview' => [
        'enabled' => (bool) env('DOCUMENT_OCR_PREVIEW', true),
        'dpi' => (int) env('DOCUMENT_OCR_PREVIEW_DPI', 150),
        'max_pages' => (int) env('DOCUMENT_OCR_PREVIEW_MAX_PAGES', 30),
        'cache_path' => env('DOCUMENT_OCR_PREVIEW_PATH', 'ocr/previews'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification
    |--------------------------------------------------------------------------
    | Values are only applied to existing records after explicit confirmation.
    */
    'apply' => [
        'allow_profile_updates' => true,
        'allow_application_updates' => true,
        'allow_household_updates' => true,
    ],
];
