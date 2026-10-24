<?php

namespace App\Http\Controllers;

use App\Services\HadAlKifayahService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HadKifayahController extends Controller
{
    public function index(): View
    {
        $rates = HadAlKifayahService::ratesMap();

        return view('hadkifayah', [
            'rates' => $rates,
        ]);
    }

    /**
     * AJAX endpoint for real-time preview.
     */
    public function preview(Request $request)
    {
        $data = $request->validate($this->hadKifayahRules());

        $result = HadAlKifayahService::calculate($data);

        return response()->json($result);
    }

    /**
     * Render a print-friendly view of the calculation result.
     */
    public function print(Request $request): View
    {
        $data = $request->validate($this->hadKifayahRules());

        $result = HadAlKifayahService::calculate($data);
        $rates = HadAlKifayahService::ratesMap();

        return view('hadkifayah-print', [
            'result' => $result,
            'rates' => $rates,
            'input' => $data,
        ]);
    }

    private function hadKifayahRules(): array
    {
        return [
            'pendapatan_pemohon' => 'nullable|numeric|min:0',
            'pendapatan_pasangan' => 'nullable|numeric|min:0',
            'pendapatan_anak' => 'nullable|numeric|min:0',
            'pendapatan_lain' => 'nullable|numeric|min:0',
            'n_isteri_suami' => 'nullable|integer|min:0|max:20',
            'n_ibu_bapa' => 'nullable|integer|min:0|max:20',
            'n_dewasa_tidak_bekerja' => 'nullable|integer|min:0|max:20',
            'n_dewasa_bekerja_tanggung' => 'nullable|integer|min:0|max:20',
            'n_ipt' => 'nullable|integer|min:0|max:20',
            'n_remaja' => 'nullable|integer|min:0|max:20',
            'n_kanak_rendah' => 'nullable|integer|min:0|max:20',
            'n_kanak_tadika' => 'nullable|integer|min:0|max:20',
            'perumahan' => 'nullable|in:sendiri,sewa',
            'utiliti_input' => 'nullable|numeric|min:0',
            'pengangkutan_input' => 'nullable|numeric|min:0',
            'perubatan_input' => 'nullable|numeric|min:0',
        ];
    }
}
