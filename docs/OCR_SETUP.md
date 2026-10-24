# OCR Setup Guide (Windows)

This system reads text from uploaded PDFs/images with a multi-engine pipeline:
1. **Smalot PdfParser** — handles PDFs that already have a text layer (digital forms, exports).
2. **Tesseract OCR** — handles scanned PDFs and image files (JPG/PNG).
3. **PHP Imagick + Ghostscript** — renders scanned PDF pages to images for Tesseract.
4. **MAIJ Document Intelligence** (`App\Services\Ocr\DocumentIntelligence`) — template-anchored
   *form understanding* for the 4-page MAIJ Zakat Assistance Application Form. Renders each page,
   reads word-coordinate OCR (Tesseract TSV), and maps filled values, checkboxes (by ink density),
   tables, signatures and the declaration into a structured `ocr_result` JSON.

**Status on this machine:** Tesseract v5.4.0 (eng+msa), ImageMagick, the PHP `imagick`
extension, and Ghostscript are all installed and working. Run `php artisan ocr:check`
to confirm they report `AVAILABLE`.

## 1. Check what you have

```powershell
php artisan ocr:check
```

Output should list Tesseract / imagick / queue worker / PHP-config status.

## 2. Single-instance `php artisan serve` (IMPORTANT)

Always start the dev server with `--no-reload` and **never start a second one** on the
same port:

```powershell
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Two serve processes racing on port 8000 cause intermittent upload failures — requests
can land on the stale instance (old code, old limits, old storage-path bug). If you
suspect duplicates, run a doctor:

```powershell
php artisan serve:doctor                          # PHP-native, cross-platform
php artisan serve:doctor --fix                    # kill duplicates, then restart serve
powershell -File scripts/serve-doctor.ps1         # PowerShell watchdog (Windows)
powershell -File scripts/serve-doctor.ps1 -Fix    # kill duplicates
```

## 2. PHP configuration (required for uploads > 2 MB)

Edit `C:\php-8.5.4\php.ini` (the file reported by `php --ini`) and ensure:

```ini
file_uploads = On
upload_max_filesize = 32M
post_max_size = 40M
memory_limit = 512M
max_input_time = 120
max_execution_time = 120
```

Restart `php artisan serve` after editing `php.ini`.

### Concurrency (Windows limitation)

`PHP_CLI_SERVER_WORKERS` does **not** work on Windows — PHP's built-in web server
(`php -S` / `php artisan serve`) is **single-threaded** on Windows and prints
`forking is not supported on this platform`. This is why the earlier guidance in
this file about setting `PHP_CLI_SERVER_WORKERS=8` was removed.

For real parallel request handling on Windows, use one of:

- **XAMPP Apache** (recommended) — a multi-threaded web server. Point a vhost's
  `DocumentRoot` at `public/` and route PHP through it.
- **Laravel Octane** — run `php artisan octane:start --server=frankenphp` (or
  RoadRunner/Swoole) for an app-server that can handle concurrent requests.
- **IIS + PHP-FPM** — if IIS is available.

In development, the upload flow still works fine on the single-threaded built-in
server because:
1. Text-layer PDFs are processed **synchronously in <1s** (fast-path).
2. Scanned PDFs / images are dispatched to the **database queue**, processed by
   the separate `queue:work` process, and the preview page polls for the result.

`upload_max_filesize` MUST be ≥ the value of `OCR_MAX_FILE_SIZE` (in KB) in your `.env`. The default is 10240 KB = 10 MB; the recommended `php.ini` value is **32 MB** to leave headroom for forms with embedded images.

## 2. Install Tesseract OCR (Windows)

1. Download the **UB Mannheim build**: https://github.com/UB-Mannheim/tesseract/wiki
   Pick the 64-bit installer.
2. Run the installer. Default path: `C:\Program Files\Tesseract-OCR\tesseract.exe`.
3. During install, select additional language packs: **Malay (`msa`)** and **English (`eng`)**.
4. Verify:

   ```powershell
   tesseract --version
   tesseract --list-langs   # should show: eng, msa, osd
   ```

5. Add to `.env` (use forward slashes to avoid dotenv escape issues):

   ```env
   TESSERACT_PATH="C:/Program Files/Tesseract-OCR/tesseract.exe"
   TESSERACT_LANG=eng+msa
   ```

## 3. Install PHP Imagick (recommended: bundled PECL DLL package)

The easiest way on Windows is the **self-contained PECL DLL zip**, which bundles
`php_imagick.dll` plus all ImageMagick `CORE_RL_*.dll`/`IM_MOD_RL_*.dll` libraries —
no separate ImageMagick install or PATH fiddling needed.

1. Check your PHP build:

   ```powershell
   php -i | findstr "Thread Safety"     # should say "enabled" if ZTS
   php -i | findstr "Architecture"      # should say "x64"
   ```

2. Download the matching DLL zip from https://windows.php.net/downloads/pecl/releases/imagick/
   - e.g. `php_imagick-3.8.1-8.5-ts-vs17-x64.zip` for PHP 8.5 ZTS x64.
   - Pick the TS/NTS and x64/x86 that matches your PHP build.
3. Extract. Copy `php_imagick.dll` into your `php\ext\` folder.
4. Copy all `CORE_RL_*.dll`, `IM_MOD_RL_*.dll`, `FILTER_*.dll` into your PHP root
   (where `php.exe` lives), and copy the `config/` folder there too.
5. Edit `php.ini` and add:

   ```ini
   extension=imagick
   ```

6. Verify:

   ```powershell
   php -m | findstr imagick
   php -r "echo class_exists('Imagick') ? 'ok' : 'missing';"
   ```

7. Also install the **ImageMagick CLI** (optional, gives you `magick`) and **Ghostscript**
   (required for PDF → image rendering) from https://www.ghostscript.com/releases.html.

> **Tip:** After installing tools, add `C:\Program Files\Tesseract-OCR` and the Ghostscript
> `bin` directory (e.g. `C:\Program Files\gs\gs10.05.0\bin`) to the system PATH.

## 3d. OCRmyPDF (optional alternative for scanned PDFs)

OCRmyPDF is a Python CLI that adds an OCR text layer back into a PDF, producing a
**searchable PDF/A**. It is an *optional, opt-in* replacement for the scanned-PDF
engine (Imagick + Tesseract). It shines on noisy/dense/handwritten scans thanks to
built-in deskew/denoise preprocessing.

Requirements:
- Python 3.10+ (e.g. `winget install Python.Python.3.12`)
- QPDF — download from https://github.com/qpdf/qpdf/releases (e.g. `qpdf-12.4.0-msvc64.zip`),
  extract, and add the `bin` folder to PATH.
- `python -m pip install ocrmypdf`

Enable it in `.env`:

```env
OCR_MY_PDF_ENABLED=true
OCR_MY_PDF_BINARY="C:/Users/akmal/AppData/Local/Programs/Python/Python312/python.exe"
OCR_MY_PDF_LANG=eng+msa
OCR_MY_PDF_TIMEOUT=120
```

When enabled, scanned PDFs (no text layer) are routed through `OcrMyPdfExtractor`,
which runs OCRmyPDF, then reads the embedded text layer back with smalot/pdfparser
and feeds it through the same field recognizers. Digital (text-layer) PDFs are
unaffected (they still use the fast path).

Check status with `php artisan ocr:check` — it will report `OCRmyPDF: READY` when
the pipeline is available.

## 3e. Mistral OCR (cloud engine)

Mistral OCR is a cloud API that reads text from PDFs and images with strong accuracy
on forms and dense documents. It is a **first-class driver** (`OCR_DRIVER=mistral`).

**Requirements (paid):** OCR is **not** on Mistral's free tier. You need:
1. A Mistral account at https://console.mistral.ai
2. A **paid workspace** (Billing → set up payment)
3. An **API key** (Billing → API Keys)

**Enable it in `.env`:**

```env
OCR_DRIVER=mistral
MISTRAL_API_KEY=your-paid-key-here
```

If `MISTRAL_API_KEY` is empty, the app logs a warning and gracefully falls back to
the local hybrid pipeline — it never breaks. Digital (text-layer) PDFs still use the
fast path.

`php artisan ocr:check` will report the Mistral driver state and whether the key is set.

> **Cost note:** Mistral OCR bills per page. Large multi-page Borang scans will incur
> a small per-page cost per upload. If you need local, cost-free OCR, keep
> `OCR_DRIVER=hybrid` (Tesseract/Imagick/OCRmyPDF).

## 4. Queue worker

OCR jobs are dispatched to the `database` queue. A worker must be running:

```powershell
php artisan queue:work --queue=default --tries=1 --timeout=120
```

For development, run this in a separate terminal alongside `php artisan serve`.
Shortcuts (add to `composer.json` scripts):

```powershell
composer queue:work       # foreground worker (Ctrl+C to stop)
composer queue:work:bg    # start a detached background worker (Windows)
composer queue:stop       # stop the background worker (Windows)
```

The background worker writes to `storage/logs/queue-worker.log` and stores its PID
in `storage/logs/queue-worker.pid`.

## 4b. Upload → poll → review flow (admin Import PDF)

The admin "Import PDF / OCR" page (`/app/import-pdf`) follows a scan → poll → review flow:

1. **Upload page** (`import-pdf/index.blade.php`) — drag-and-drop. On load it calls
   `/app/import-pdf/preflight` to show a health warning if all OCR engines are down,
   if there are stalled jobs, or if the queue is backed up.
2. **Upload** — the file is POSTed to `/app/import-pdf/upload` via `fetch()` (JSON),
   which returns `{ scan_id, status_url, review_url }`.
3. **Polling** — the page polls `/app/import-pdf/status/{id}` every 1.5s. The
   `ProcessDocumentImport` job writes a `progress` column (10 → 25 → 30 → 70 → 85 → 100)
   that drives the stage labels on the progress bar. Stalled (>180s) and failed states
   are surfaced with retry.
4. **Review page** (`import-pdf/review.blade.php`) — a two-column layout: left shows the
   original file, collapsible raw OCR text, and the AI confidence score; right shows the
   editable extracted fields, each with a confidence badge (green ≥90%, amber ≥75%,
   red <75%). "Sah & Simpan" POSTs to `/app/import-pdf/commit/{id}`.

### MAIJ Document Intelligence

For uploads with document type `borang_bantuan_maij`, the queue pipeline additionally
runs `BorangFormUnderstanding` after text extraction:

- Renders each page at `ocr.document_intelligence.word_dpi` (default 220) via Imagick.
- Runs Tesseract TSV (`--psm 6`) to obtain word-level bounding boxes and confidence.
- `MaiFormTemplate` maps the 4-page form layout (field zones, checkbox groups, tables,
  signature/date zones) — coordinates are in a reference space (1870×2420) and any scan
  is normalised into it.
- Values are extracted from zones, normalised (`ValueNormalizer`), classified as
  printed/typed/handwritten (`InputTypeDetector`), and every field carries `page`,
  `bbox`, `input_type`, `original_value`, `confidence` and `needs_review`.
- Checkboxes are detected by **ink density** inside each box, compared against a
  calibrated blank-template baseline (tri-state `true`/`false`/`null`).
- The result is stored in `document_imports.ocr_result` and shown on the preview page as
  "Maklumat Berjaya Dikesan" (bantuan table, checkbox chips, dependents, income/expenses,
  declaration/verification + signature presence).

Configuration (`.env` / `config/ocr.php` → `document_intelligence`):

| Key | Default | Purpose |
|---|---|---|
| `OCR_DOCUMENT_INTELLIGENCE` | `true` | Enable the form-understanding pipeline |
| `OCR_WORD_DPI` | `220` | Render resolution for word-coordinate OCR |
| `OCR_CHECKBOX_PROBE` | `true` | Ink-density checkbox detection (needs Imagick) |
| `OCR_TEXTLAYER_FALLBACK` | `true` | Use the text layer for pages that render blank |
| `OCR_TEMPLATE_PDF` | `resources/ocr/templates/MAIJ-Borang-Agihan-Zakat-blank.pdf` | Blank 4-page template used for calibration |
| `OCR_CALIBRATION_PATH` | `resources/ocr/templates/calibration.json` | Measured checkbox/signature baselines |

The pipeline never guesses: unreadable values become `null` with `needs_review: true`
and the human review step is never skipped.

### Text-layer (digital AcroForm) extraction

Some filled MAIJ PDFs are vector/digital (Adobe Fill & Sign) — their page 2 (or
other pages) does not rasterise via Imagick, so zone OCR yields nothing. In that
case the pipeline falls back to the page's **text layer**, which contains only the
filled values in visual reading order (no labels).

`App\Services\Ocr\DocumentIntelligence\TextLayerSequenceParser` consumes that token
stream using a type-aware matcher spec (`MaiFormTemplate::textLayerSpecs()`):
multi-token names, grouped account numbers ("5555 5555"), dates ("Feb 2026") and
12-digit ICs are matched by pattern, so a page like

```
1500 5555 5555 8888 8888 MUHAMMAD DANIAL BIN SHAHARUDDIN Saudara
040311084031 Sakit Jiwa Feb 2026 Hospital Skudai 15000 20000
Muhammad Akmal Hafiz bin Shamsul Bahare 0123232
```

is mapped to `jumlah_sara_hidup=1500`, `no_akaun_bimb=5555 5555`,
`perubatan_nama_tanggungan=MUHAMMAD DANIAL BIN SHAHARUDDIN`,
`perubatan_no_kp_tanggungan=040311084031`, `perubatan_kos_rawatan=15000`, … .
Unmatched values become `null` with `needs_review: true` — it never guesses.

### Vision AI document understanding (GPT-Vision style)

Instead of template-anchored OCR, the pipeline can ask a **vision model** to read
the rendered page images and return structured JSON. Each page is rendered to an
image (`App\Services\Ocr\DocumentIntelligence\Vision\PageImageRenderer`), sent to
the model (`VisionFormUnderstanding`), and the reply is normalised into the same
`ocr_result` contract — grouped by section (application, applicant, sara_hidup,
medical, business, housing, debt, dependents, residence, assets, employment,
income, expenses, declaration, verification) with per-field `value`,
`confidence`, `page`, `bbox`, `input_type` and `needs_review`.

**Enable it in `.env`:**

```env
OCR_VISION_ENABLED=true
OCR_VISION_PROVIDER=openai        # openai | gemini | ollama | mock
# OpenAI (GPT Vision):
AI_OPENAI_API_KEY=sk-...
OCR_VISION_OPENAI_MODEL=gpt-4o-mini
# or Gemini:
# AI_GEMINI_API_KEY=...
# OCR_VISION_GEMINI_MODEL=gemini-2.0-flash
# or Ollama (local, free — pull a vision model first):
# OCR_VISION_PROVIDER=ollama
# OCR_VISION_OLLAMA_MODEL=qwen2.5vl:7b
```

Other knobs: `OCR_VISION_DPI=150`, `OCR_VISION_MAX_PAGES=4`,
`OCR_VISION_TIMEOUT=120`, `OCR_VISION_CONFIDENCE=60`,
`OCR_VISION_FALLBACK_LOCAL=true`.

Behavior:
- **Fallback** — when the provider is not configured, or the vision call fails,
  the pipeline silently falls back to the local template-anchored engine, so
  uploads always produce results. Set `OCR_VISION_FALLBACK_LOCAL=false` to fail
  instead.
- **Never guesses** — unreadable values → `null` + `needs_review`; low-confidence
  fields are flagged for the human review step (never skipped).
- The prompt (`MaiFormVisionSchema`) embeds the MAIJ form structure (all 15
  sections, field keys, checkbox groups, dependents table, signature/date zones)
  and the FIELD LABEL → FIELD VALUE mapping rule.
- The review page shows a "Vision AI (provider)" badge; `php artisan ocr:check`
  reports vision readiness.

### Blank template & baseline calibration

The system ships with the blank 4-page MAIJ form at
`resources/ocr/templates/MAIJ-Borang-Agihan-Zakat-blank.pdf` (used as the reference
for checkbox/signature ink-density detection) and a measured calibration file at
`resources/ocr/templates/calibration.json`.

To re-measure the blank-box baselines after any template change:

```powershell
php artisan ocr:calibrate-template            # measure + write calibration.json
php artisan ocr:calibrate-template --dry-run  # measure, print, don't write
php artisan ocr:calibrate-template --json     # print raw JSON
```

The command renders every page of the template, locates each checkbox box, and
records its "empty" ink density plus the signature/stamp zone densities. Labels
that can't be OCR'd on a page are reported as warnings (a sign of template drift).
`MaiFormTemplate::checkboxBaselines()` / `inkZones()` load these values at runtime
and fall back to the built-in defaults if the JSON is missing.

To audit the blank template and prove the full form is covered by the field map:

```powershell
php artisan ocr:template-detect            # sections, fields, checkboxes + unmapped labels
php artisan ocr:template-detect --json     # machine-readable report
```

The audit renders all 4 pages, checks every field/checkbox/section in
`MaiFormTemplate` (FOUND/MISSING), and lists any printed labels the field map does
**not** cover — surfacing candidate missing fields (e.g. the supporting-documents
checklist, section-3 column headers, income-row labels) for a human to add.

### Financial audit (Audit Kewangan)

Both the local and vision engines now also extract the income/expense **totals**
(`JUMLAH PENDAPATAN (RM)` / `JUMLAH PERBELANJAAN (RM)`) and run a financial
consistency audit via `App\Services\Ocr\DocumentIntelligence\FinancialAudit`:

- `income_sum` / `expense_sum` — the summed row values.
- **Checks** — Jumlah Pendapatan vs income rows; Jumlah Perbelanjaan vs expense
  rows; balance (perbelanjaan vs pendapatan); main `pendapatan` vs `pendapatan_diri`.
- Each check is `pass` / `warn` / `fail` / `skip`; the block has `overall` and
  `needs_review` (any `fail` → `fail`, any `warn` → `warn`).

The result is stored in `ocr_result.financial_audit`, shown as an **"Audit
Kewangan"** card in the review UI, and persisted into `borang_data`. Income and
expense amounts are read from separate table columns (income ≈ x560–900, expenses
≈ x1300–1830 on the reference page) so a value in one column is never misread into
the other.

Admins can download/print the blank form from `/app/import-pdf` ("Muat Turun Borang
Kosong") — the route is behind `auth:web` and downloads are audit-logged.

Run `php artisan migrate` once after adding the `progress` column migration.

## 5. Verify everything

```powershell
php artisan ocr:check
```

You should see `Tesseract: AVAILABLE` and `imagick extension: AVAILABLE`.

Then upload a test PDF through `http://127.0.0.1:8000/app/import-pdf`.

## Troubleshooting

| Symptom | Cause |
|---|---|
| `Tesseract: NOT AVAILABLE` | Binary not installed, or `TESSERACT_PATH` env var is wrong. |
| `imagick: NOT AVAILABLE` | DLL not loaded — check `extension=imagick` in `php.ini`. |
| Upload returns 200 but preview shows "File not found" | The OCR pipeline is using the wrong storage path; run `php artisan ocr:check` and verify the `Storage::disk('local')->path()` matches where your files are written. |
| Preview spinner never finishes | Queue worker is not running. Start it with `composer queue:work` (or `composer queue:work:bg`). |
| Confidence is very low (<30%) on a clear PDF | The PDF may be a scanned image — install Tesseract + Imagick. |
| `upload_max_filesize (too small for configured limit of …)` in `ocr:check` | `php.ini` `upload_max_filesize` is below `OCR_MAX_FILE_SIZE` (KB). Raise the php.ini value to at least the same size, restart `php artisan serve`. |
| Page hangs on "Memuat naik…" with no progress | PHP's built-in server is single-threaded on Windows (`PHP_CLI_SERVER_WORKERS` is unsupported). The status-poller can't run while a long request is in flight. Since the OCR fast-path + async queue are in place, this should no longer happen; if it does, restart `php artisan serve` and confirm the queue worker (`php artisan queue:work`) is running. |
| `Undefined constant "uploading"` in `laravel.log` | Stale Blade view cache or `x-app-button` prop collision — run `php artisan view:clear` and reload. |
