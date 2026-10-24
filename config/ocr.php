<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default OCR text extraction driver
    |--------------------------------------------------------------------------
    | 'tesseract' — uses thiagoalessio/tesseract_ocr (requires tesseract binary)
    | 'pdfparser' — uses smalot/pdfparser for text-layer PDFs
    | 'hybrid'    — tries pdfparser first for PDFs, falls back to tesseract
    | 'mistral'   — uses the Mistral OCR cloud API (requires MISTRAL_API_KEY)
    */
    'default' => env('OCR_DRIVER', 'hybrid'),

    /*
    |--------------------------------------------------------------------------
    | AI Field Recognizer
    |--------------------------------------------------------------------------
    | Which AI provider to use for intelligent field mapping from raw OCR text.
    | Uses the same providers as config('ai.default'): mock, openai, gemini, ollama.
    | Set to empty string to skip AI layer (regex-only).
    */
    'ai_recognizer' => env('OCR_AI_RECOGNIZER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Confidence threshold (percentage)
    |--------------------------------------------------------------------------
    | Fields with confidence below this threshold are flagged for manual review.
    */
    'confidence_threshold' => (int) env('OCR_CONFIDENCE_THRESHOLD', 70),

    /*
    |--------------------------------------------------------------------------
    | MAIJ Form Understanding / Document Intelligence
    |--------------------------------------------------------------------------
    | Template-anchored form understanding for the 4-page MAIJ Zakat Assistance
    | Application Form. Renders each PDF page to an image (Imagick), reads
    | word-coordinate OCR (Tesseract TSV), and maps filled values/checkboxes
    | into structured fields using the MaiFormTemplate field map.
    |
    | word_dpi        — render resolution used for coordinate OCR.
    | checkbox_probe  — pixel-density detection for checkboxes (needs Imagick).
    | fallback_text   — when a page renders blank but has a text layer, use the
    |                   text-layer tokens mapped by the template field order.
    |--------------------------------------------------------------------------
    */
    'document_intelligence' => [
        'enabled' => (bool) env('OCR_DOCUMENT_INTELLIGENCE', true),
        'word_dpi' => (int) env('OCR_WORD_DPI', 220),
        'checkbox_probe' => (bool) env('OCR_CHECKBOX_PROBE', true),
        'fallback_text' => (bool) env('OCR_TEXTLAYER_FALLBACK', true),
        'signature_zone_ink' => 0.01,
        /*
        |--------------------------------------------------------------------------
        | MAIJ blank template + calibration
        |--------------------------------------------------------------------------
        | template_pdf      — the blank 4-page MAIJ form used as the reference for
        |                     checkbox/signature ink-density calibration.
        | calibration_path  — JSON produced by `php artisan ocr:calibrate-template`,
        |                     containing the measured blank-box baselines that
        |                     MaiFormTemplate::checkboxBaselines() loads at runtime.
        */
        'template_pdf' => env('OCR_TEMPLATE_PDF', resource_path('ocr/templates/MAIJ-Borang-Agihan-Zakat-blank.pdf')),
        'calibration_path' => env('OCR_CALIBRATION_PATH', resource_path('ocr/templates/calibration.json')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vision AI document understanding (GPT-Vision style)
    |--------------------------------------------------------------------------
    | When enabled and a provider is configured, the MAIJ pipeline renders each
    | page to an image and asks a vision-capable model to understand the form
    | (field label → value, handwriting, checkboxes, tables, signatures, dates)
    | and return structured JSON grouped by section. When no provider is
    | configured, or a call fails, the pipeline falls back to the local
    | Tesseract template path (ocr.document_intelligence).
    |
    | provider — openai | gemini | ollama | mock
    | render_dpi — page render resolution for the vision model.
    | max_pages — hard cap on how many pages are sent per upload.
    | fallback_local — run the local pipeline when vision fails (default true).
    |--------------------------------------------------------------------------
    */
    'vision' => [
        'enabled' => (bool) env('OCR_VISION_ENABLED', false),
        'provider' => env('OCR_VISION_PROVIDER', 'openai'),
        'render_dpi' => (int) env('OCR_VISION_DPI', 150),
        'max_pages' => (int) env('OCR_VISION_MAX_PAGES', 4),
        'timeout' => (int) env('OCR_VISION_TIMEOUT', 120),
        'fallback_local' => (bool) env('OCR_VISION_FALLBACK_LOCAL', true),
        'confidence_threshold' => (int) env('OCR_VISION_CONFIDENCE', 60),
        'openai' => [
            'api_key' => env('AI_OPENAI_API_KEY', ''),
            'model' => env('OCR_VISION_OPENAI_MODEL', 'gpt-4o-mini'),
            'base' => env('AI_OPENAI_BASE', 'https://api.openai.com/v1'),
        ],
        'gemini' => [
            'api_key' => env('AI_GEMINI_API_KEY', ''),
            'model' => env('OCR_VISION_GEMINI_MODEL', 'gemini-2.0-flash'),
        ],
        'ollama' => [
            'host' => env('AI_OLLAMA_HOST', 'http://127.0.0.1:11434'),
            'model' => env('OCR_VISION_OLLAMA_MODEL', 'qwen2.5vl:7b'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tesseract configuration
    |--------------------------------------------------------------------------
    */
    'tesseract' => [
        'path' => env('TESSERACT_PATH', 'tesseract'),
        'language' => env('TESSERACT_LANG', 'eng+msa'),
        'psm' => (int) env('TESSERACT_PSM', 3),
        'oem' => (int) env('TESSERACT_OEM', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | File upload limits
    |--------------------------------------------------------------------------
    */
    'max_file_size_kb' => (int) env('OCR_MAX_FILE_SIZE', 10240),
    'allowed_mimes' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
    ],
    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],

    /*
    |--------------------------------------------------------------------------
    | Storage disk for uploaded documents.
    |--------------------------------------------------------------------------
    */
    'disk' => env('OCR_STORAGE_DISK', 'local'),
    'path' => env('OCR_STORAGE_PATH', 'ocr'),

    /*
    |--------------------------------------------------------------------------
    | Temporary image conversion for PDF pages (requires Imagick or Ghostscript)
    |--------------------------------------------------------------------------
    */
    'pdf_to_image' => [
        'driver' => env('OCR_PDF_IMAGE_DRIVER', 'tesseract'),
        'dpi' => (int) env('OCR_PDF_IMAGE_DPI', 220),
        'max_pages' => (int) env('OCR_PDF_MAX_PAGES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image preprocessing before Tesseract (requires ImageMagick / Imagick)
    |--------------------------------------------------------------------------
    | When enabled, scanned images and PDF pages are normalised before OCR:
    | grayscale, auto-contrast, upscaling of small scans and deskew. This
    | typically improves accuracy on low-quality scans at a small CPU cost.
    |--------------------------------------------------------------------------
    */
    'preprocess' => [
        'enabled' => (bool) env('OCR_PREPROCESS', false),
        'min_size' => (int) env('OCR_PREPROCESS_MIN_SIZE', 1600),
        'grayscale' => true,
        'contrast' => true,
        'deskew' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Page markers in concatenated multi-page OCR text
    |--------------------------------------------------------------------------
    | When enabled, pages are joined with "--- PAGE n ---" separators so the
    | section-based recognizers can tell where one page ends and the next
    | begins. Helps stop fields bleeding across page boundaries.
    |--------------------------------------------------------------------------
    */
    'page_markers' => [
        'enabled' => (bool) env('OCR_PAGE_MARKERS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request / sync processing limits
    |--------------------------------------------------------------------------
    | sync_fast_path_enabled — process text-layer PDFs synchronously in the
    |   upload request so digital forms return results instantly.
    | sync_fast_path_max_kb — skip the fast path for very large PDFs and
    |   defer them to the queue instead.
    | sync_request_timeout_seconds — max wall-clock for a synchronous OCR run
    |   inside an HTTP request.
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'sync_fast_path_enabled' => (bool) env('OCR_SYNC_FAST_PATH', true),
        'sync_fast_path_max_kb' => (int) env('OCR_SYNC_FAST_PATH_MAX_KB', 5120),
        'sync_request_timeout_seconds' => (int) env('OCR_SYNC_TIMEOUT', 60),
        // sync_scan_enabled — when true, small scanned images/PDFs (no text
        // layer) are OCR'd synchronously in the upload request via Tesseract
        // instead of waiting for the queue. Opt-in: default off keeps the
        // existing queued behaviour.
        'sync_scan_enabled' => (bool) env('OCR_SYNC_SCAN', false),
        'sync_scan_max_kb' => (int) env('OCR_SYNC_SCAN_MAX_KB', 2048),
    ],

    /*
    |--------------------------------------------------------------------------
    | OCRmyPDF (alternative scanned-PDF engine)
    |--------------------------------------------------------------------------
    | When enabled, scanned PDFs are routed through OCRmyPDF (a Python CLI that
    | adds an OCR text layer to the PDF, producing a searchable PDF/A) instead
    | of the Imagick+Tesseract pipeline. Requires Python + OCRmyPDF + QPDF on
    | the system. The text is then re-extracted via smalot/pdfparser and fed
    | through the same field recognizers.
    |
    | binary — python executable to invoke OCRmyPDF via `-m ocrmypdf`, or a
    |   full path to ocrmypdf(.exe). Use forward slashes on Windows.
    |--------------------------------------------------------------------------
    */
    'ocr_my_pdf' => [
        'enabled' => (bool) env('OCR_MY_PDF_ENABLED', false),
        'binary' => env('OCR_MY_PDF_BINARY', 'python'),
        'language' => env('OCR_MY_PDF_LANG', 'eng+msa'),
        'timeout' => (int) env('OCR_MY_PDF_TIMEOUT', 120),
        'options' => ['--deskew', '--optimize', '1'],
        'tessdata_prefix' => env('OCR_MY_PDF_TESSDATA_PREFIX', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mistral OCR (cloud engine)
    |--------------------------------------------------------------------------
    | Used when OCR_DRIVER=mistral. Requires a paid Mistral API key (OCR is not
    | on the free tier). Calls POST https://api.mistral.ai/v1/ocr with the key
    | in the Authorization header. If MISTRAL_API_KEY is empty, OcrService logs
    | a warning and falls back to the hybrid/local pipeline.
    |
    | base_url — Mistral API base (default https://api.mistral.ai).
    | model — the OCR model id.
    | timeout — HTTP timeout in seconds.
    |--------------------------------------------------------------------------
    */
    'mistral' => [
        'api_key' => env('MISTRAL_API_KEY', ''),
        'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai'),
        'model' => env('MISTRAL_MODEL', 'mistral-ocr-latest'),
        'timeout' => (int) env('MISTRAL_TIMEOUT', 60),
        'max_pages' => (int) env('MISTRAL_MAX_PAGES', 10),
        'retry_attempts' => (int) env('MISTRAL_RETRY_ATTEMPTS', 2),
        'retry_backoff_ms' => (int) env('MISTRAL_RETRY_BACKOFF_MS', 1000),
    ],
];
