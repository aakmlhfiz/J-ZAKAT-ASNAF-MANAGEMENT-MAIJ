<?php

namespace App\Http\Controllers;

use App\Models\ImportHistory;
use App\Models\ProfilAsnaf;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function index(): View
    {
        $history = ImportHistory::latest()->limit(10)->get();
        $stats = [
            'total' => ImportHistory::sum('jumlah_rekod') ?: 0,
            'berjaya' => ImportHistory::sum('berjaya') ?: 0,
            'gagal' => ImportHistory::sum('gagal') ?: 0,
        ];

        return view('importcsv', compact('history', 'stats'));
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'nama', 'ic', 'ic_ketua_rumah', 'hubungan', 'telefon', 'alamat',
            'pendapatan', 'bilangan_tanggungan', 'sewa', 'utiliti',
            'n_isteri_suami', 'n_ibu_bapa', 'n_dewasa_tidak_bekerja',
            'n_dewasa_bekerja_tanggung', 'n_ipt', 'n_remaja',
            'n_kanak_rendah', 'n_kanak_tadika', 'perumahan',
            'pengangkutan_input', 'perubatan_input',
        ];
        $sample = [
            'Ali Bin Abu', '900101011234', '900101011234', 'Ketua Keluarga',
            '0123456789', 'Lot 1 Kg Padang Tembak', 1800, 4, 300, 120,
            0, 0, 0, 0, 0, 0, 0, 0, 'sendiri', 0, 0,
        ];

        $callback = function () use ($headers, $sample) {
            $f = fopen('php://output', 'w');
            fputcsv($f, $headers);
            fputcsv($f, $sample);
            fclose($f);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="templat_pemohon.csv"',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => 'required|file|mimetypes:text/csv,text/plain|max:10240',
        ]);

        $path = $request->file('csv')->getRealPath();
        $rows = array_map(fn ($r) => str_getcsv($r), file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        if (count($rows) < 2) {
            return back()->with('error', __('imports.messages.csv_empty'));
        }

        $header = array_map('trim', array_shift($rows));
        $jumlah = 0;
        $berjaya = 0;
        $gagal = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                $jumlah++;

                if (count($row) < count($header)) {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_short', ['row' => $i + 2]);

                    continue;
                }

                $data = array_combine($header, $row);
                $ic = trim($data['ic'] ?? '');
                $nama = trim($data['nama'] ?? '');

                if ($ic === '' || $nama === '') {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_missing_identity', ['row' => $i + 2]);

                    continue;
                }

                if (trim($data['hubungan'] ?? '') === '') {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_missing_relationship', ['row' => $i + 2]);

                    continue;
                }

                if (trim($data['telefon'] ?? '') === '') {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_missing_phone', ['row' => $i + 2]);

                    continue;
                }

                if (trim($data['alamat'] ?? '') === '') {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_missing_address', ['row' => $i + 2]);

                    continue;
                }

                if (! is_numeric($data['pendapatan'] ?? '')) {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_invalid_income', ['row' => $i + 2]);

                    continue;
                }

                if (! is_numeric($data['bilangan_tanggungan'] ?? '')) {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_invalid_dependents', ['row' => $i + 2]);

                    continue;
                }

                if (ProfilAsnaf::where('ic', $ic)->exists()) {
                    $gagal++;
                    $errors[] = __('imports.messages.csv_row_duplicate_ic', ['row' => $i + 2, 'ic' => $ic]);

                    continue;
                }

                $profil = new ProfilAsnaf([
                    'nama' => $nama,
                    'ic' => $ic,
                    'ic_ketua_rumah' => trim($data['ic_ketua_rumah'] ?? '') ?: null,
                    'hubungan' => trim($data['hubungan'] ?? '') ?: null,
                    'telefon' => trim($data['telefon'] ?? '') ?: null,
                    'alamat' => trim($data['alamat'] ?? '') ?: null,
                    'pendapatan' => (float) ($data['pendapatan'] ?? 0),
                    'bilangan_tanggungan' => (int) ($data['bilangan_tanggungan'] ?? 0),
                    'sewa' => (float) ($data['sewa'] ?? 0),
                    'utiliti' => (float) ($data['utiliti'] ?? 0),
                    'n_isteri_suami' => (int) ($data['n_isteri_suami'] ?? 0),
                    'n_ibu_bapa' => (int) ($data['n_ibu_bapa'] ?? 0),
                    'n_dewasa_tidak_bekerja' => (int) ($data['n_dewasa_tidak_bekerja'] ?? 0),
                    'n_dewasa_bekerja_tanggung' => (int) ($data['n_dewasa_bekerja_tanggung'] ?? 0),
                    'n_ipt' => (int) ($data['n_ipt'] ?? 0),
                    'n_remaja' => (int) ($data['n_remaja'] ?? 0),
                    'n_kanak_rendah' => (int) ($data['n_kanak_rendah'] ?? 0),
                    'n_kanak_tadika' => (int) ($data['n_kanak_tadika'] ?? 0),
                    'perumahan' => in_array($data['perumahan'] ?? '', ['sewa', 'sendiri'], true) ? $data['perumahan'] : 'sendiri',
                    'pengangkutan_input' => (float) ($data['pengangkutan_input'] ?? 0),
                    'perubatan_input' => (float) ($data['perubatan_input'] ?? 0),
                ]);

                EligibilityService::apply($profil);
                $profil->save();
                $berjaya++;
            }

            ImportHistory::create([
                'nama_fail' => $request->file('csv')->getClientOriginalName(),
                'jumlah_rekod' => $jumlah,
                'berjaya' => $berjaya,
                'gagal' => $gagal,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $berjaya = 0;
            $gagal = $jumlah;
        }

        $icon = $gagal > 0 ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-file-csv';
        $color = $gagal > 0 ? 'warning' : 'success';

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'import_completed',
            __('imports.messages.csv_notification_title'),
            __('imports.messages.csv_notification_body', [
                'success' => $berjaya,
                'failed' => $gagal,
                'time' => now()->diffForHumans(),
            ]),
            $icon,
            $color,
            route('importcsv')
        );

        $message = __('imports.messages.csv_completed', [
            'success' => $berjaya,
            'failed' => $gagal,
            'total' => $jumlah,
        ]);
        if (! empty($errors)) {
            $message .= ' '.implode(' ', array_slice($errors, 0, 3));
        }

        AuditLogger::csvImported(auth('web')->user(), [
            'file_name' => $request->file('csv')->getClientOriginalName(),
            'total_rows' => $jumlah,
            'success_count' => $berjaya,
            'failed_count' => $gagal,
            'errors_count' => count($errors),
        ], $request);

        return back()->with($gagal === $jumlah ? 'error' : 'success', $message);
    }
}
