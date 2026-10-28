<?php

namespace App\Http\Controllers;

use App\Models\HadKifayahSetting;
use App\Services\HadAlKifayahService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HadKifayahSettingController extends Controller
{
    public function index(): View
    {
        $settings = HadKifayahSetting::orderBy('kategori')->orderBy('id')->get();
        $grouped = $settings->groupBy('kategori');

        return view('hadkifayah-settings', [
            'grouped' => $grouped,
            'order' => $this->kategoriOrder(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rows' => 'required|array',
            'rows.*.id' => 'required|integer|exists:had_kifayah_settings,id',
            'rows.*.nilai' => 'required|numeric|min:0',
            'rows.*.status' => 'required|in:active,inactive',
        ]);

        foreach ($data['rows'] as $row) {
            HadKifayahSetting::where('id', $row['id'])->update([
                'nilai' => $row['nilai'],
                'status' => $row['status'],
            ]);
        }

        HadAlKifayahService::bustCache();

        $ketuaKeluargaRow = collect($data['rows'])->first(function ($row) {
            $setting = HadKifayahSetting::find($row['id']);

            return $setting && $setting->kategori === 'Ketua Keluarga';
        });
        $nilaiKetua = $ketuaKeluargaRow ? number_format((float) $ketuaKeluargaRow['nilai'], 2) : null;

        NotificationService::notifyRoles(
            ['admin', 'pegawai'],
            'had_kifayah_updated',
            __('hadkifayah.notification.updated_title'),
            $nilaiKetua
                ? __('hadkifayah.notification.updated_body', [
                    'value' => $nilaiKetua,
                    'time' => now()->locale(app()->getLocale())->diffForHumans(),
                ])
                : now()->locale(app()->getLocale())->diffForHumans(),
            'fa-solid fa-calculator',
            'primary',
            route('hadkifayah.settings')
        );

        return redirect()
            ->route('hadkifayah.settings')
            ->with('success', __('hadkifayah.settings.updated'));
    }

    /**
     * Display order for the admin form (matches the calculator's input order).
     */
    private function kategoriOrder(): array
    {
        return [
            'Ketua Keluarga',
            'Dewasa',
            'IPT',
            'Remaja',
            'Kanak-Kanak',
            'Rumah Sewa',
            'Utiliti',
            'Pengangkutan',
            'Perubatan',
        ];
    }
}
