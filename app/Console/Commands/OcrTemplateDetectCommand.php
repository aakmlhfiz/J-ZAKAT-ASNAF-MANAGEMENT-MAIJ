<?php

namespace App\Console\Commands;

use App\Services\Ocr\DocumentIntelligence\CheckboxDetector;
use App\Services\Ocr\DocumentIntelligence\MaiFormTemplate;
use App\Services\Ocr\DocumentIntelligence\TesseractWordExtractor;
use App\Services\Ocr\DocumentIntelligence\WordGrid;
use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Console\Command;

/**
 * "Detect all form" — audits the blank MAIJ template PDF and reports every
 * field/checkbox/section the field map covers, plus any printed labels the map
 * does NOT cover (potential missing fields). Read-only; writes nothing.
 */
class OcrTemplateDetectCommand extends Command
{
    protected $signature = 'ocr:template-detect
        {--json : Emit the full report as JSON instead of tables}';

    protected $description = 'Audit the blank MAIJ borang template and list every detected field, checkbox, section and any unmapped labels';

    /** Static phrases to ignore when reporting unmapped labels. */
    private const STOPWORDS = [
        'MAJLIS AGAMA ISLAM NEGERI JOHOR', 'BAHAGIAN AGIHAN', 'AGIHAN ZAKAT', 'ZAKAT',
        'Aras', 'Blok', 'Pusat Islam Iskandar Johor', 'Jalan Masjid Abu Bakar', 'Masjid Abu Bakar',
        '80990 Johor Bahru', 'Johor Bahru', 'No. Tel', 'No. Fax', 'Email', 'zakat@maij.gov.my',
        'PERHATIAN', 'Ruangan', 'WAJIB', 'wajib', 'diisi', 'jika', 'berkenaan', 'sahaja',
        'PERCUMA', 'pemohon', 'dokumen permohonan', 'pengesahan pendapatan', 'pendapatan pemohon',
        'yang dipohon', 'sesungguhnya', 'Sebarang pertanyaan', 'sila hubungi', 'Sabda Rasulullah',
        'Sebaik-baik manusia', 'MAJLIS AGAMA ISLAM DAERAH', 'Jalan Petri', 'Jalan Bakau Condong',
        'Batu 1', 'Jalan Pejabat Kerajaan', 'Aras Bawah', 'Aras 5', 'Kompleks Pusat Bandar',
        'Masjid Bandar Tangkak', 'Pejabat Kadi Daerah', 'No. 2930', 'Jalan Merbau', 'Tandatangan',
        'Dengan Nama', 'keterangan yang', 'sekiranya butiran', 'Pengesahan ini boleh',
        'Wakil Rakyat', 'sila isi maklumat bertanda', 'mestilah diakui', 'turut.',
        'mengikut jenis permohonan', 'yang pons', 'di Negeri Johor', 'rujuk senarai semak',
    ];

    /** Phone / footer noise patterns — skipped as unmapped labels. */
    private const NOISE_PATTERNS = [
        '/(?:Tel|Faks)\s*[:\/]?\s*\d/i',
        '/\b\d{2,3}[\s\-]?\d{3,4}[\s\-]?\d{3,4}\b/',
        '/^Jalan\s+\d/i',
        '/^\d{4,5}\s+\w/i',
    ];

    public function handle(): int
    {
        $template = (string) config('ocr.document_intelligence.template_pdf');

        if (! file_exists($template)) {
            $this->error("Template PDF tidak dijumpai: {$template}");

            return self::FAILURE;
        }

        if (! class_exists(\Imagick::class) || ! TesseractExtractor::isAvailable()) {
            $this->error('Audit memerlukan Imagick dan Tesseract.');

            return self::FAILURE;
        }

        $extractor = new TesseractWordExtractor;
        $pages = $extractor->extractPages($template, 'application/pdf');

        if (empty($pages)) {
            $this->error('Tiada halaman dapat dirender dari template.');

            return self::FAILURE;
        }

        $grids = [];
        foreach ($pages as $page) {
            $grids[$page['page']] = new WordGrid($page['words'], $page['width'], $page['height']);
        }

        $report = [
            'template' => basename($template),
            'pages' => count($pages),
            'sections' => $this->detectSections($grids),
            'fields' => $this->detectFields($grids),
            'checkboxes' => $this->detectCheckboxes($grids),
            'unmapped_labels' => $this->detectUnmappedLabels($grids),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('=== Audit Template Borang MAIJ ===');
        $this->line('Template : '.basename($template));
        $this->line('Halaman  : '.count($pages));
        $this->newLine();

        $sections = $report['sections'];
        $this->info('SEKSYEN DIKESAN ('.count($sections).' / '.count(MaiFormTemplate::sections()).')');
        $this->table(['Halaman', 'No', 'Seksyen'], array_map(
            fn ($s) => ['Muka '.$s['page'], $s['number'], $s['title']],
            $sections
        ));
        $this->newLine();

        $fields = $report['fields'];
        $found = count(array_filter($fields, fn ($f) => $f['status'] === 'FOUND'));
        $this->info("MEDAN DIKESAN ({$found} / ".count($fields).')');
        $this->table(['Halaman', 'Medan', 'Status', 'Label'], array_map(
            fn ($f) => ['Muka '.$f['page'], $f['key'], $f['status'], $f['label']],
            $fields
        ));
        $this->newLine();

        $checkboxes = $report['checkboxes'];
        $this->info('KOTAK SEMAK DIKESAN');
        foreach ($checkboxes as $group => $options) {
            $foundOptions = array_filter($options, fn ($o) => $o['found']);
            $this->line("  {$group}: ".count($foundOptions).' / '.count($options).' ditemui');
            foreach ($options as $optKey => $opt) {
                $this->line('    - '.$optKey.': '.($opt['found'] ? '<info>FOUND</info>' : '<comment>MISSING</comment>'));
            }
        }
        $this->newLine();

        $unmapped = $report['unmapped_labels'];
        $this->info('LABEL TIDAK DIPETAKAN ('.count($unmapped).') — calon medan baharu');
        if (empty($unmapped)) {
            $this->line('  Tiada.');
        } else {
            foreach ($unmapped as $line) {
                $this->line('  [Muka '.$line['page'].'] '.$line['text']);
            }
        }

        return self::SUCCESS;
    }

    private function detectSections(array $grids): array
    {
        $found = [];
        foreach (MaiFormTemplate::sections() as $section) {
            $grid = $grids[$section['page']] ?? null;
            if (! $grid || $grid->isEmpty()) {
                continue;
            }
            if ($this->sectionMatches($grid->plainText(), $section['title'])) {
                $found[] = ['number' => $section['number'], 'title' => $section['title'], 'page' => $section['page']];
            }
        }

        return $found;
    }

    private function detectFields(array $grids): array
    {
        $out = [];
        foreach (MaiFormTemplate::fields() as $key => $spec) {
            $grid = $grids[$spec['page']] ?? null;
            $label = $spec['label'];
            $found = $grid && ! $grid->isEmpty() && $this->labelWordsPresent($grid, $label);
            $out[] = [
                'key' => $key,
                'label' => $label,
                'page' => $spec['page'],
                'status' => $found ? 'FOUND' : 'MISSING',
            ];
        }

        return $out;
    }

    private function detectCheckboxes(array $grids): array
    {
        $out = [];
        foreach (MaiFormTemplate::checkboxGroups() as $groupKey => $group) {
            $group['key'] = $groupKey;
            $page = $group['page'];
            $grid = $grids[$page] ?? null;

            if (! $grid || $grid->isEmpty()) {
                $out[$groupKey] = array_fill_keys(array_keys($group['options']), ['found' => false]);

                continue;
            }

            $detector = new CheckboxDetector($grid, $page, $group, null, MaiFormTemplate::fixedCheckboxZones(), []);
            $zones = $detector->zones();

            $out[$groupKey] = [];
            foreach (array_keys($group['options']) as $optionKey) {
                $out[$groupKey][$optionKey] = ['found' => $zones[$optionKey] !== null];
            }
        }

        return $out;
    }

    private function detectUnmappedLabels(array $grids): array
    {
        $unmapped = [];

        foreach ($grids as $page => $grid) {
            if ($grid->isEmpty()) {
                continue;
            }

            $covered = $this->coveredRegions($grid, $page);
            $words = array_filter($grid->words(), fn ($w) => $w['conf'] >= 55 && $this->isLabelWord($w['text']));

            $candidates = [];
            foreach ($words as $word) {
                if (! $this->wordCovered($word, $covered)) {
                    $candidates[] = $word;
                }
            }

            foreach ($this->clusterLines($candidates) as $line) {
                $text = $this->cleanLine($line);
                if ($text === null || $this->isStopword($text)) {
                    continue;
                }
                $unmapped[] = ['page' => $page, 'text' => $text];
            }
        }

        return $unmapped;
    }

    /** Union of field/checkbox/section regions (reference space) that "explain" a word. */
    private function coveredRegions(WordGrid $grid, int $page): array
    {
        $regions = [];

        foreach (MaiFormTemplate::fields() as $spec) {
            if ($spec['page'] !== $page || $spec['zone'] === null) {
                continue;
            }
            [$x, $y, $w, $h] = $spec['zone'];
            $regions[] = ['x' => $x - 260, 'y' => $y - 45, 'w' => $w + 300, 'h' => $h + 90];
        }

        foreach (MaiFormTemplate::checkboxGroups() as $groupKey => $group) {
            if ($group['page'] !== $page) {
                continue;
            }
            $group['key'] = $groupKey;
            $detector = new CheckboxDetector($grid, $page, $group, null, MaiFormTemplate::fixedCheckboxZones(), []);
            foreach ($detector->zones() as $zone) {
                if ($zone !== null) {
                    $regions[] = ['x' => $zone['x'] - 70, 'y' => $zone['y'] - 15, 'w' => $zone['w'] + 220, 'h' => $zone['h'] + 40];
                }
            }
        }

        // Dependents table band (page 3).
        if ($page === 3) {
            $table = MaiFormTemplate::dependentsTable();
            $regions[] = ['x' => 0, 'y' => $table['y_top'] - 60, 'w' => 1700, 'h' => $table['y_bottom'] - $table['y_top'] + 90];
        }

        // Section title lines.
        foreach (MaiFormTemplate::sections() as $section) {
            if ($section['page'] !== $page) {
                continue;
            }
            $labelWord = $grid->findLabel([$section['title']]);
            if ($labelWord !== null) {
                $regions[] = ['x' => $labelWord['l'] - 20, 'y' => $labelWord['t'] - 15, 'w' => 1200, 'h' => $labelWord['h'] + 30];
            }
        }

        return $regions;
    }

    private function wordCovered(array $word, array $regions): bool
    {
        $cx = $word['l'] + (int) round($word['w'] / 2);
        $cy = $word['t'] + (int) round($word['h'] / 2);

        foreach ($regions as $r) {
            if ($cx >= $r['x'] && $cx <= $r['x'] + $r['w'] && $cy >= $r['y'] && $cy <= $r['y'] + $r['h']) {
                return true;
            }
        }

        return false;
    }

    private function labelWordsPresent(WordGrid $grid, string $label): bool
    {
        $words = preg_split('/\s+/', $label);
        foreach ($words as $word) {
            if (mb_strlen($word) < 4) {
                continue;
            }
            if ($grid->findLabel([$word]) !== null) {
                return true;
            }
        }

        return false;
    }

    private function isLabelWord(string $text): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9\-\/\.&()]+$/', $text) && mb_strlen($text) >= 2;
    }

    private function clusterLines(array $words, int $tolerance = 14): array
    {
        usort($words, fn ($a, $b) => $a['t'] <=> $b['t'] ?: $a['l'] <=> $b['l']);

        $lines = [];
        $current = [];
        $lastTop = null;
        foreach ($words as $word) {
            if ($lastTop === null || abs($word['t'] - $lastTop) <= $tolerance) {
                $current[] = $word;
                $lastTop = $word['t'];
            } else {
                $lines[] = $current;
                $current = [$word];
                $lastTop = $word['t'];
            }
        }
        if (! empty($current)) {
            $lines[] = $current;
        }

        return $lines;
    }

    private function cleanLine(array $words): ?string
    {
        usort($words, fn ($a, $b) => $a['l'] <=> $b['l']);
        $text = trim(implode(' ', array_map(fn ($w) => $w['text'], $words)));

        if ($text === '' || mb_strlen($text) < 6) {
            return null;
        }

        if (preg_match('/^[\d\s\.\-\(\)\/]+$/', $text)) {
            return null;
        }

        return $text;
    }

    private function isStopword(string $text): bool
    {
        $lower = mb_strtolower($text);
        foreach (self::STOPWORDS as $stop) {
            if (mb_strpos($lower, mb_strtolower($stop)) !== false) {
                return true;
            }
        }
        foreach (self::NOISE_PATTERNS as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    private function sectionMatches(string $pageText, string $title): bool
    {
        $norm = function (string $s): string {
            $s = preg_replace('/[^a-z]/', '', mb_strtolower($s));
            $out = '';
            $prev = '';
            foreach (str_split($s) as $ch) {
                if ($ch !== $prev) {
                    $out .= $ch;
                }
                $prev = $ch;
            }

            return $out;
        };

        return mb_strpos($norm($pageText), $norm($title)) !== false;
    }
}
