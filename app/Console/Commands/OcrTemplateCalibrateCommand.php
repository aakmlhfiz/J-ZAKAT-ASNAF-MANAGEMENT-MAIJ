<?php

namespace App\Console\Commands;

use App\Services\Ocr\DocumentIntelligence\CheckboxDetector;
use App\Services\Ocr\DocumentIntelligence\MaiFormTemplate;
use App\Services\Ocr\DocumentIntelligence\TesseractWordExtractor;
use App\Services\Ocr\DocumentIntelligence\WordGrid;
use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Calibrate the MAIJ blank-form template: measure the ink density of every
 * checkbox box (and signature/stamp zone) on the blank 4-page form and write
 * the results to the configured calibration JSON. The recognition pipeline
 * reads these baselines at runtime via MaiFormTemplate::checkboxBaselines().
 */
class OcrTemplateCalibrateCommand extends Command
{
    protected $signature = 'ocr:calibrate-template
        {--dry-run : Print the calibration without writing the JSON file}
        {--json : Emit the calibration as raw JSON instead of the table}';

    protected $description = 'Calibrate checkbox/signature ink baselines from the blank MAIJ borang template PDF';

    public function handle(): int
    {
        $template = (string) config('ocr.document_intelligence.template_pdf');
        $calibrationPath = (string) config('ocr.document_intelligence.calibration_path');

        if (! file_exists($template)) {
            $this->error("Template PDF tidak dijumpai: {$template}");

            return self::FAILURE;
        }

        if (! class_exists(\Imagick::class)) {
            $this->error('Imagick tidak dipasang — kalibrasi memerlukan Imagick untuk merender halaman.');

            return self::FAILURE;
        }

        if (! TesseractExtractor::isAvailable()) {
            $this->error('Tesseract tidak dijumpai pada pelayan — kalibrasi memerlukan enjin OCR.');

            return self::FAILURE;
        }

        $extractor = new TesseractWordExtractor;
        $pages = $extractor->extractPages($template, 'application/pdf');

        if (empty($pages)) {
            $this->error('Tiada halaman dapat dirender dari template PDF.');

            return self::FAILURE;
        }

        $grids = [];
        foreach ($pages as $page) {
            $grids[$page['page']] = new WordGrid($page['words'], $page['width'], $page['height']);
        }

        $density = function (int $x, int $y, int $w, int $h, int $page) use ($extractor, $pages): ?float {
            $info = $pages[$page - 1] ?? null;
            if (! $info) {
                return null;
            }
            $sx = $info['width'] / MaiFormTemplate::$referenceWidth;
            $sy = $info['height'] / MaiFormTemplate::$referenceHeight;

            return $extractor->density($page, (int) round($x * $sx), (int) round($y * $sy), max(1, (int) round($w * $sx)), max(1, (int) round($h * $sy)));
        };

        $checkboxBaselines = [];
        $warnings = [];

        foreach (MaiFormTemplate::checkboxGroups() as $groupKey => $group) {
            $group['key'] = $groupKey;
            $page = $group['page'];
            $grid = $grids[$page] ?? null;

            if (! $grid || $grid->isEmpty()) {
                $warnings[] = "Halaman {$page} tidak menghasilkan teks OCR untuk kumpulan '{$groupKey}'.";

                continue;
            }

            $detector = new CheckboxDetector($grid, $page, $group, $density, MaiFormTemplate::fixedCheckboxZones(), []);

            foreach ($detector->zones() as $optionKey => $zone) {
                $key = $groupKey.'.'.$optionKey;

                if ($zone === null) {
                    $warnings[] = "Label kotak semak '{$optionKey}' (kumpulan '{$groupKey}') tidak dijumpai pada muka {$page}.";

                    continue;
                }

                $baseline = $density($zone['x'], $zone['y'], $zone['w'], $zone['h'], $page);
                $checkboxBaselines[$key] = $baseline !== null ? round($baseline, 4) : null;
            }
        }

        $inkZones = [];
        foreach (MaiFormTemplate::inkZones() as $key => $zone) {
            $z = $zone['zone'];
            $ratio = $density($z['x'], $z['y'], $z['w'], $z['h'], $zone['page']);
            $inkZones[$key] = $ratio !== null ? round($ratio, 4) : null;
        }

        $calibration = [
            'source_pdf' => basename($template),
            'page_count' => count($pages),
            'calibrated_at' => now()->toIso8601String(),
            'checkbox_baselines' => $checkboxBaselines,
            'ink_zones' => $inkZones,
            'warnings' => $warnings,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($calibration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->line('Kalibrasi Template Borang MAIJ');
        $this->line('=============================');
        $this->line('Template : '.basename($template));
        $this->line('Halaman  : '.count($pages));
        $this->line('Dikira   : '.$calibration['calibrated_at']);
        $this->newLine();

        if (! empty($checkboxBaselines)) {
            $rows = [];
            foreach ($checkboxBaselines as $key => $value) {
                $rows[] = [$key, $value === null ? '—' : number_format($value, 4)];
            }
            $this->table(['Kotak Semak (baseline)', 'Ketumpatan'], $rows);
        }

        if (! empty($inkZones)) {
            $this->table(['Zon Dakwat', 'Ketumpatan'], array_map(
                fn ($k, $v) => [$k, $v === null ? '—' : number_format($v, 4)],
                array_keys($inkZones),
                array_values($inkZones)
            ));
        }

        foreach ($warnings as $warning) {
            $this->warn($warning);
        }

        if ($this->option('dry-run')) {
            $this->info('Mod dry-run — fail kalibrasi TIDAK ditulis.');

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($calibrationPath));
        File::put($calibrationPath, json_encode($calibration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Kalibrasi ditulis ke: {$calibrationPath}");

        return self::SUCCESS;
    }
}
