<?php

namespace App\Services\Ocr;

use App\Models\DocumentImport;
use App\Models\ProfilAsnaf;
use App\Services\EligibilityService;
use App\Services\Ocr\Contracts\TextExtractor;
use App\Services\Ocr\DocumentIntelligence\BorangFormUnderstanding;
use App\Services\Ocr\DocumentIntelligence\Vision\VisionFormUnderstanding;
use App\Services\Ocr\Extractors\MistralOcrExtractor;
use App\Services\Ocr\Extractors\OcrMyPdfExtractor;
use App\Services\Ocr\Extractors\PdfOcrExtractor;
use App\Services\Ocr\Extractors\PdfTextExtractor;
use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    public const DOC_TYPE_BORANG_BANTUAN = 'borang_bantuan_maij';

    public const DOC_TYPE_GENERIC = 'generic';

    private RegexFieldRecognizer $regexRecognizer;

    private SmartFieldRecognizer $smartRecognizer;

    private BorangBantuanRecognizer $borangRecognizer;

    private string $driver;

    public function __construct(
        ?RegexFieldRecognizer $regexRecognizer = null,
        ?SmartFieldRecognizer $smartRecognizer = null,
        ?BorangBantuanRecognizer $borangRecognizer = null,
    ) {
        $this->regexRecognizer = $regexRecognizer ?? new RegexFieldRecognizer;
        $this->smartRecognizer = $smartRecognizer ?? new SmartFieldRecognizer;
        $this->borangRecognizer = $borangRecognizer ?? new BorangBantuanRecognizer;
        $this->driver = config('ocr.default', 'hybrid');
    }

    /**
     * Process a DocumentImport: extract text → recognize fields → update record.
     */
    public function process(DocumentImport $import): void
    {
        $filePath = $this->resolveImportPath($import);

        if (! file_exists($filePath)) {
            $import->update([
                'status' => 'failed',
                'error_message' => 'Fail tidak dijumpai pada storan. Sila muat naik semula dokumen ini.',
            ]);

            return;
        }

        $import->update(['ocr_started_at' => now(), 'status' => 'processing', 'progress' => 10]);

        $startedAt = microtime(true);

        try {
            $pageAnalyzer = new PdfPageAnalyzer;
            $pageInfo = $pageAnalyzer->analyze($filePath, $import->mime_type);
            $this->setProgress($import, 25);

            $extractor = $this->resolveExtractor($import->mime_type, $filePath);
            $this->setProgress($import, 30);

            $ocrText = $extractor->extract($filePath);

            $this->finalizeExtraction($import, $ocrText, $extractor, $pageInfo, $startedAt, true);
        } catch (\Throwable $e) {
            logger()->error('OCR processing failed', [
                'import_id' => $import->id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'elapsed_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            // Re-throw so the queued job can retry. The job marks the import
            // as permanently failed on its final attempt.
            throw $e;
        }
    }

    private function setProgress(DocumentImport $import, int $progress): void
    {
        $import->update(['progress' => $progress]);
    }

    /**
     * Synchronous fast-path for text-layer PDFs.
     *
     * Extracts text with Smalot PdfParser directly inside the upload request
     * so digital (form-filled) PDFs return instant results instead of waiting
     * for the queue. Returns false when the PDF has no usable text layer, in
     * which case the caller should fall back to the queued job.
     */
    public function processTextLayer(DocumentImport $import): bool
    {
        if (($import->mime_type ?? null) !== 'application/pdf') {
            return false;
        }

        if (! config('ocr.limits.sync_fast_path_enabled', true)) {
            return false;
        }

        $maxKb = (int) config('ocr.limits.sync_fast_path_max_kb', 5120);
        if ($import->file_size > $maxKb * 1024) {
            return false;
        }

        $filePath = $this->resolveImportPath($import);

        if (! file_exists($filePath)) {
            return false;
        }

        $startedAt = microtime(true);

        try {
            $extractor = new PdfTextExtractor;
            $ocrText = $extractor->extract($filePath);

            if (empty(trim($ocrText))) {
                return false;
            }

            $pageAnalyzer = new PdfPageAnalyzer;
            $pageInfo = $pageAnalyzer->analyze($filePath, $import->mime_type);

            $import->update(['ocr_started_at' => now(), 'status' => 'processing', 'progress' => 10]);

            $this->finalizeExtraction($import, $ocrText, $extractor, $pageInfo, $startedAt);

            logger()->info('OCR fast-path (text layer) completed', [
                'import_id' => $import->id,
                'elapsed_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            return true;
        } catch (\Throwable $e) {
            logger()->error('OCR fast-path (text layer) failed', [
                'import_id' => $import->id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Synchronous fast-path for small scanned documents (images or scanned
     * PDFs with no text layer) using local Tesseract. Opt-in via
     * ocr.limits.sync_scan_enabled so the default remains queued processing.
     */
    public function processScanFastPath(DocumentImport $import): bool
    {
        if (! config('ocr.limits.sync_scan_enabled', false)) {
            return false;
        }

        if (! TesseractExtractor::isAvailable()) {
            return false;
        }

        $mime = $import->mime_type ?? null;
        if (! str_starts_with($mime, 'image/') && $mime !== 'application/pdf') {
            return false;
        }

        $maxKb = (int) config('ocr.limits.sync_scan_max_kb', 2048);
        if ($import->file_size > $maxKb * 1024) {
            return false;
        }

        $filePath = $this->resolveImportPath($import);

        if (! file_exists($filePath)) {
            return false;
        }

        // For PDFs only proceed when there is no usable text layer (a real scan).
        if ($mime === 'application/pdf') {
            try {
                $text = (new PdfTextExtractor)->extract($filePath);
                if (trim($text) !== '') {
                    return false;
                }
            } catch (\Throwable $e) {
                // No text layer — treat as scanned.
            }
        }

        $startedAt = microtime(true);

        try {
            $import->update(['ocr_started_at' => now(), 'status' => 'processing', 'progress' => 10]);

            $extractor = $this->resolveExtractor($mime, $filePath);
            $ocrText = $extractor->extract($filePath);

            if (empty(trim($ocrText))) {
                return false;
            }

            $pageAnalyzer = new PdfPageAnalyzer;
            $pageInfo = $pageAnalyzer->analyze($filePath, $mime);

            $this->finalizeExtraction($import, $ocrText, $extractor, $pageInfo, $startedAt);

            logger()->info('OCR scan fast-path completed', [
                'import_id' => $import->id,
                'elapsed_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            return true;
        } catch (\Throwable $e) {
            logger()->error('OCR scan fast-path failed', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function finalizeExtraction(DocumentImport $import, string $ocrText, TextExtractor $extractor, array $pageInfo, float $startedAt, bool $runIntelligence = false): void
    {
        if (empty(trim($ocrText))) {
            $import->update([
                'status' => 'failed',
                'error_message' => $this->noTextMessage($import),
                'ocr_engine' => $extractor->name(),
                'progress' => 0,
                'ocr_completed_at' => now(),
                'page_count' => $pageInfo['page_count'],
            ]);

            return;
        }

        $this->setProgress($import, 70);

        // Single recognition pass: regex → AI enhance (once), then borang
        // merging reuses those results instead of re-running regex + LLM.
        $regexResult = $this->regexRecognizer->recognize($ocrText);
        $regexConfidence = (float) ($regexResult['overall_confidence'] ?? 0);

        $enhancedResult = $regexConfidence >= 90
            ? $regexResult
            : $this->smartRecognizer->enhance($regexResult, $ocrText);

        $borangData = null;

        if (($import->document_type ?? null) === self::DOC_TYPE_BORANG_BANTUAN) {
            $borangResult = $this->borangRecognizer->recognizeFrom($ocrText, $regexResult, $enhancedResult);
            $enhancedResult = $borangResult;
            $borangData = $borangResult['borang'] ?? null;
        }

        $extractedFields = $enhancedResult['fields'] ?? $regexResult['fields'] ?? [];
        $extractedMembers = $enhancedResult['household_members'] ?? $regexResult['household_members'] ?? [];

        // MAIJ form understanding (template-anchored document intelligence).
        $ocrResult = null;
        if ($runIntelligence && $this->intelligenceEnabled($import)) {
            $ocrResult = $this->runDocumentIntelligence($import, $ocrText, $pageInfo);
            if ($ocrResult !== null) {
                $extractedFields = $this->mergeIntelligenceFields($extractedFields, $ocrResult);
            }
        }

        $overallConfidence = $enhancedResult['overall_confidence'] ?? $regexConfidence;
        $newStatus = $this->decideStatus($extractedFields, $overallConfidence);

        $this->setProgress($import, 85);

        $updatePayload = [
            'status' => $newStatus,
            'progress' => 100,
            'ocr_text' => $ocrText,
            'ocr_engine' => $extractor->name(),
            'ocr_completed_at' => now(),
            'extracted_data' => $extractedFields,
            'overall_confidence' => $overallConfidence,
            'matched_labels' => $enhancedResult['matched_labels'] ?? $regexResult['matched_labels'],
            'household_members_extracted' => $extractedMembers,
            'page_count' => $pageInfo['page_count'],
        ];

        if ($borangData !== null) {
            $updatePayload['borang_data'] = $borangData;
        }

        if ($ocrResult !== null) {
            $updatePayload['ocr_result'] = $ocrResult;
        }

        $import->update($updatePayload);

        $elapsed = round(microtime(true) - $startedAt, 2);
        logger()->info('OCR processing completed', [
            'import_id' => $import->id,
            'engine' => $extractor->name(),
            'elapsed_seconds' => $elapsed,
            'document_type' => $import->document_type,
            'overall_confidence' => $overallConfidence,
        ]);
    }

    private function intelligenceEnabled(DocumentImport $import): bool
    {
        if (! (bool) config('ocr.document_intelligence.enabled', true)) {
            return false;
        }

        if (($import->document_type ?? null) !== self::DOC_TYPE_BORANG_BANTUAN) {
            return false;
        }

        return class_exists(\Imagick::class);
    }

    /**
     * Run MAIJ document understanding. Prefers the Vision AI pipeline when it is
     * enabled and configured; falls back to the local template-anchored engine
     * on any failure. Returns the structured ocr_result, or null when neither
     * can run. Never throws.
     */
    private function runDocumentIntelligence(DocumentImport $import, string $ocrText, array $pageInfo): ?array
    {
        try {
            $filePath = $this->resolveImportPath($import);
            if (! file_exists($filePath)) {
                return null;
            }

            // Vision-AI first (GPT-Vision style document understanding).
            $vision = new VisionFormUnderstanding;
            if ($vision->supported()) {
                $visionResult = $vision->understand($filePath, $import->mime_type, $ocrText, $pageInfo);
                if ($visionResult !== null) {
                    return $visionResult;
                }

                if (! (bool) config('ocr.vision.fallback_local', true)) {
                    return null;
                }
            }

            $understanding = new BorangFormUnderstanding;

            return $understanding->understand($filePath, $import->mime_type, $ocrText, $pageInfo);
        } catch (\Throwable $e) {
            logger()->error('MAIJ form understanding failed, falling back to regex/AI', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fold high-confidence understanding results into extracted_data so the
     * legacy preview/commit paths still see the applicant identity fields.
     * Only fills fields that are currently empty.
     */
    private function mergeIntelligenceFields(array $extractedFields, array $ocrResult): array
    {
        $keys = ['nama', 'ic', 'alamat', 'daerah', 'poskod', 'bandar', 'dun', 'parlimen', 'no_telefon_bimbit', 'no_telefon_rumah'];

        foreach ($keys as $key) {
            $field = $ocrResult['fields'][$key] ?? null;
            if (! $field || ($field['value'] ?? null) === null) {
                continue;
            }
            $existingValue = $extractedFields[$key]['value'] ?? null;
            if ($existingValue !== null && $existingValue !== '') {
                continue;
            }
            $extractedFields[$key] = [
                'value' => $field['value'],
                'confidence' => (int) ($field['confidence'] ?? 0),
                'source_label' => $field['source_label'] ?? $key,
                'needs_review' => (bool) ($field['needs_review'] ?? true),
                'page' => $field['page'] ?? null,
                'bbox' => $field['bbox'] ?? null,
                'input_type' => $field['input_type'] ?? null,
            ];
        }

        // Checkbox-derived identity attributes (Sihat/Sakit/Cacat, marital, smoking).
        $cb = $ocrResult['checkboxes'] ?? [];
        $checkedOf = fn (array $group) => collect($group)->first(fn ($r) => ($r['value'] ?? null) === true);

        if (empty($extractedFields['kesihatan']['value'] ?? null)) {
            $hit = $checkedOf($cb['kesihatan'] ?? []);
            if ($hit) {
                $key = array_search($hit, $cb['kesihatan'] ?? [], true);
                $extractedFields['kesihatan'] = [
                    'value' => ucfirst((string) $key),
                    'confidence' => $hit['confidence'] ?? 0,
                    'needs_review' => ($hit['confidence'] ?? 0) < 60,
                ];
            }
        }

        if (empty($extractedFields['status_perkahwinan']['value'] ?? null)) {
            $hit = $checkedOf($cb['status_perkahwinan'] ?? []);
            if ($hit) {
                $key = array_search($hit, $cb['status_perkahwinan'] ?? [], true);
                $map = ['bujang' => 'Bujang', 'berkahwin' => 'Berkahwin', 'janda' => 'Janda', 'duda' => 'Duda'];
                $extractedFields['status_perkahwinan'] = [
                    'value' => $map[$key] ?? ucfirst($key),
                    'confidence' => $hit['confidence'] ?? 0,
                    'needs_review' => ($hit['confidence'] ?? 0) < 60,
                ];
            }
        }

        if (empty($extractedFields['merokok']['value'] ?? null)) {
            $hit = $checkedOf($cb['merokok'] ?? []);
            if ($hit) {
                $key = array_search($hit, $cb['merokok'] ?? [], true);
                $extractedFields['merokok'] = [
                    'value' => $key === 'ya' ? 'Ya' : 'Tidak',
                    'confidence' => $hit['confidence'] ?? 0,
                    'needs_review' => ($hit['confidence'] ?? 0) < 60,
                ];
            }
        }

        return $extractedFields;
    }

    private function noTextMessage(DocumentImport $import): string
    {
        $isPdf = ($import->mime_type ?? null) === 'application/pdf';

        if ($isPdf) {
            return 'Tiada teks dapat diekstrak. PDF ini kemungkinan diimbas (imej) — ia memerlukan Tesseract/ImageMagick. '
                .'Pasang Tesseract OCR dan/atau ImageMagick, kemudian cuba proses semula.';
        }

        if (! TesseractExtractor::isAvailable()) {
            return 'Tiada teks dapat diekstrak. Enjin OCR (Tesseract) tidak dipasang pada pelayan. '
                .'Pasang Tesseract OCR (https://github.com/UB-Mannheim/tesseract/wiki) dan cuba semula.';
        }

        return 'No text could be extracted from the file. The image may be too low quality or the PDF may not contain readable text.';
    }

    /**
     * Decide whether an extraction is safe to auto-accept. Uses the configured
     * confidence threshold and requires the critical identity fields (nama, ic)
     * to be present — otherwise it is flagged for manual review.
     */
    private function decideStatus(array $fields, float $confidence): string
    {
        $threshold = (int) config('ocr.confidence_threshold', 70);

        $nama = $fields['nama']['value'] ?? null;
        $ic = $fields['ic']['value'] ?? null;

        if ($confidence < $threshold || empty($nama) || empty($ic)) {
            return 'needs_review';
        }

        return 'extracted';
    }

    /**
     * User-facing failure message. Internal exception details (which can
     * contain absolute paths) are logged, not shown to the user.
     */
    public function friendlyErrorMessage(\Throwable $e): string
    {
        if (! TesseractExtractor::isAvailable() && str_contains($e->getMessage(), 'Tesseract')) {
            return 'Pemprosesan OCR gagal: enjin Tesseract tidak dipasang pada pelayan. Pasang Tesseract OCR dan cuba semula.';
        }

        return 'Pemprosesan OCR gagal semasa mengekstrak teks. Sila cuba proses semula atau muat naik dokumen yang lebih jelas.';
    }

    /**
     * Create a new DocumentImport record from uploaded file metadata.
     */
    public function createImportRecord(array $data): DocumentImport
    {
        return DocumentImport::create([
            'user_id' => auth('web')->id(),
            'original_filename' => $data['original_filename'],
            'stored_path' => $data['stored_path'],
            'mime_type' => $data['mime_type'],
            'file_size' => $data['file_size'],
            'file_hash' => $data['file_hash'],
            'status' => 'processing',
        ]);
    }

    /**
     * Commit extracted data into the profil_asnafs table with full household linkage.
     */
    public function commit(DocumentImport $import, array $overrides): ProfilAsnaf
    {
        $extracted = $import->extracted_data ?? [];

        $data = [
            'nama' => $overrides['nama'] ?? $extracted['nama']['value'] ?? '',
            'ic' => $overrides['ic'] ?? $extracted['ic']['value'] ?? '',
            'ic_ketua_rumah' => $overrides['ic_ketua_rumah'] ?? $extracted['ic_ketua_rumah']['value'] ?? null,
            'hubungan' => $overrides['hubungan'] ?? $extracted['hubungan']['value'] ?? null,
            'status_perkahwinan' => $overrides['status_perkahwinan'] ?? $extracted['status_perkahwinan']['value'] ?? null,
            'merokok' => isset($overrides['merokok']) ? filter_var($overrides['merokok'], FILTER_VALIDATE_BOOLEAN) : (
                isset($extracted['merokok']['value']) ? $this->parseBoolean($extracted['merokok']['value']) : false
            ),
            'telefon' => $overrides['telefon'] ?? $extracted['telefon']['value'] ?? null,
            'no_telefon_bimbit' => $overrides['no_telefon_bimbit'] ?? $extracted['no_telefon_bimbit']['value'] ?? null,
            'no_telefon_rumah' => $overrides['no_telefon_rumah'] ?? $extracted['no_telefon_rumah']['value'] ?? null,
            'kesihatan' => $overrides['kesihatan'] ?? $extracted['kesihatan']['value'] ?? null,
            'alamat' => $overrides['alamat'] ?? $extracted['alamat']['value'] ?? null,
            'daerah' => $overrides['daerah'] ?? $extracted['daerah']['value'] ?? null,
            'bandar' => $overrides['bandar'] ?? $extracted['bandar']['value'] ?? null,
            'poskod' => $overrides['poskod'] ?? $extracted['poskod']['value'] ?? null,
            'dun' => $overrides['dun'] ?? $extracted['dun']['value'] ?? null,
            'parlimen' => $overrides['parlimen'] ?? $extracted['parlimen']['value'] ?? null,
            'pendapatan' => (float) ($overrides['pendapatan'] ?? $extracted['pendapatan']['value'] ?? 0),
            'bilangan_tanggungan' => (int) ($overrides['bilangan_tanggungan'] ?? $extracted['bilangan_tanggungan']['value'] ?? 0),
            'sewa' => (float) ($overrides['sewa'] ?? $extracted['sewa']['value'] ?? 0),
            'utiliti' => (float) ($overrides['utiliti'] ?? $extracted['utiliti']['value'] ?? 0),
            'pengangkutan_input' => (float) ($overrides['pengangkutan_input'] ?? $extracted['pengangkutan']['value'] ?? 0),
            'perubatan_input' => (float) ($overrides['perubatan_input'] ?? $extracted['perubatan']['value'] ?? 0),
            'sebab_memohon' => $overrides['sebab_memohon'] ?? $extracted['sebab_memohon_bantuan']['value'] ?? null,
        ];

        $profil = ProfilAsnaf::create($data);

        if (($import->document_type ?? null) === self::DOC_TYPE_BORANG_BANTUAN) {
            $profil->borang_data = $this->buildBorangDataPayload($import, $extracted);
        }

        EligibilityService::apply($profil);
        $profil->save();

        $import->update([
            'status' => 'committed',
            'profil_asnaf_ic' => $profil->ic,
        ]);

        return $profil;
    }

    private function resolveExtractor(string $mimeType, string $filePath): TextExtractor
    {
        $isPdf = ($mimeType === 'application/pdf');

        // Mistral cloud OCR driver. If the API key is not configured, log a
        // warning and gracefully fall back to the hybrid/local pipeline so the
        // app never breaks just because the key is absent.
        if ($this->driver === 'mistral') {
            if (MistralOcrExtractor::isConfigured()) {
                return new MistralOcrExtractor;
            }

            logger()->warning('OCR_DRIVER=mistral set but MISTRAL_API_KEY is missing — falling back to hybrid driver.');
        }

        if ($this->driver === 'pdfparser' && $isPdf) {
            return new PdfTextExtractor;
        }

        if ($this->driver === 'tesseract' || ! $isPdf) {
            return new TesseractExtractor;
        }

        if ($isPdf) {
            try {
                $pdfExtractor = new PdfTextExtractor;
                $text = $pdfExtractor->extract($filePath);

                if (! empty(trim($text))) {
                    return $pdfExtractor;
                }
            } catch (\Throwable $e) {
            }

            // Scanned PDF (no text layer). If OCRmyPDF is enabled, route it
            // through the Python OCRmyPDF engine (produces a searchable PDF);
            // otherwise fall back to Imagick+Tesseract, then Tesseract directly.
            if (config('ocr.ocr_my_pdf.enabled', false)) {
                try {
                    return new OcrMyPdfExtractor;
                } catch (\Throwable $e) {
                }
            }

            if (class_exists(\Imagick::class)) {
                try {
                    return new PdfOcrExtractor;
                } catch (\Throwable $e) {
                }
            }

            return new TesseractExtractor;
        }

        return new TesseractExtractor;
    }

    private function parseBoolean(string $value): bool
    {
        $lower = mb_strtolower(trim($value));

        return in_array($lower, ['ya', 'yes', 'y', 'true', '1', '✓', '✔', '☑'], true);
    }

    /**
     * Resolve the absolute path to a DocumentImport's stored file, honouring
     * the configured OCR storage disk (whose root may live under
     * storage/app/private in Laravel 11+).
     */
    private function resolveImportPath(DocumentImport $import): string
    {
        $disk = config('ocr.disk', 'local');

        return Storage::disk($disk)->path($import->stored_path);
    }

    /**
     * Compile the Borang Bantuan MAIJ payload that will be persisted on
     * profil_asnafs.borang_data. Pulls section metadata from the import
     * record (borang_data) and field values from extracted_data.
     */
    private function buildBorangDataPayload(DocumentImport $import, array $extracted): array
    {
        $payload = [
            'document_type' => $import->document_type,
            'template' => $import->template,
            'sections' => $import->borang_data ?? [],
            'fields' => [],
        ];

        $borangFields = [
            'jumlah_sara_hidup', 'no_akaun_bimb', 'bantuan_agensi_lain_ada', 'bantuan_agensi_lain_nyatakan', 'bantuan_agensi_lain_jumlah', 'tidak_pernah_terima_bantuan',
            'perubatan_jenis_rawatan', 'perubatan_pemohon_or_tanggungan', 'perubatan_nama_tanggungan', 'perubatan_hubungan_tanggungan', 'perubatan_no_kp_tanggungan', 'perubatan_jenis_penyakit', 'perubatan_mula_mengidap', 'perubatan_tempat_rawatan', 'perubatan_kos_rawatan', 'perubatan_harga_alatan',
            'jenis_berdikari', 'berdikari_jenis_peralatan', 'berdikari_harga_peralatan', 'berdikari_jenis_perniagaan', 'berdikari_sebab_memerlukan', 'berdikari_pengalaman_ada', 'berdikari_pengalaman_tahun',
            'jenis_bantuan_perumahan', 'status_tanah', 'nama_dalam_geran', 'no_geran', 'perumahan_laporan_polis',
            'sebab_berhutang', 'jumlah_hutang', 'sebab_gagal_bayar_hutang', 'nama_pemberi_hutang', 'no_kp_pemberi_hutang',
            'tempat_tinggal_sendiri', 'tempat_tinggal_menumpang', 'tempat_tinggal_menyewe', 'nilai_tempat_tinggal', 'lain_lain_nyatakan_tempat_tinggal',
            'harta_rumah', 'harta_unit_rumah', 'harta_nilai_rumah', 'harta_tanah', 'harta_ekar', 'harta_nilai_tanah', 'harta_kereta', 'harta_unit_kereta', 'harta_nilai_kereta', 'harta_motosikal', 'harta_unit_motosikal', 'harta_nilai_motosikal',
            'pekerjaan_pemohon_nama', 'pekerjaan_pemohon_no_kp', 'pekerjaan_pemohon_majikan', 'pekerjaan_pemohon_jawatan', 'pekerjaan_pemohon_pendapatan',
            'pekerjaan_pasangan_nama', 'pekerjaan_pasangan_no_kp', 'pekerjaan_pasangan_majikan', 'pekerjaan_pasangan_jawatan', 'pekerjaan_pasangan_pendapatan',
            'pendapatan_diri', 'pendapatan_pasangan_ibubapa_penjaga', 'pendapatan_pencen_perkeso', 'pendapatan_sumbangan_anak', 'pendapatan_bantuan_agensi', 'pendapatan_lain_lain', 'jumlah_pendapatan',
            'perbelanjaan_makan_minum', 'perbelanjaan_bil_api_air', 'perbelanjaan_persekolahan', 'perbelanjaan_sewa_ansuran',
            'bantuan_sara_hidup', 'bantuan_perubatan', 'bantuan_berdikari', 'bantuan_perumahan', 'menjelaskan_hutang', 'jumlah_bantuan_diperlukan', 'sebab_memohon_bantuan',
        ];

        foreach ($borangFields as $field) {
            if (isset($extracted[$field])) {
                $payload['fields'][$field] = $extracted[$field];
            }
        }

        // Merge the structured form-understanding result (applications, medical,
        // business, housing, debt, dependents, income, expenses, declaration,
        // verification) when the MAIJ document-intelligence pipeline produced it.
        if (! empty($import->ocr_result)) {
            $ocr = $import->ocr_result;
            $structure = ['applications', 'medical', 'business', 'housing', 'debt', 'dependents', 'assets', 'employment', 'income', 'expenses', 'financial_audit', 'declaration', 'verification'];
            foreach ($structure as $key) {
                if (isset($ocr[$key])) {
                    $payload[$key] = $ocr[$key];
                }
            }
            if (isset($ocr['document'])) {
                $payload['document'] = $ocr['document'];
            }
        }

        return $payload;
    }
}
