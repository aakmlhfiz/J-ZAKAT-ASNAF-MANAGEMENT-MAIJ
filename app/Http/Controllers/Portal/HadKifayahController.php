<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\HadAlKifayahService;
use Illuminate\Http\Request;

class HadKifayahController extends Controller
{
    public function index()
    {
        $user = auth('portal')->user();
        $rates = HadAlKifayahService::ratesMap();

        return view('portal.hadkifayah', compact('user', 'rates'));
    }

    public function calculate(Request $request)
    {
        $user = auth('portal')->user();
        $rates = HadAlKifayahService::ratesMap();

        $validated = $request->validate([
            'pendapatan_pemohon' => ['required', 'numeric', 'min:0'],
            'pendapatan_pasangan' => ['nullable', 'numeric', 'min:0'],
            'pendapatan_anak' => ['nullable', 'numeric', 'min:0'],
            'pendapatan_lain' => ['nullable', 'numeric', 'min:0'],
            'n_isteri_suami' => ['nullable', 'integer', 'min:0', 'max:5'],
            'n_ibu_bapa' => ['nullable', 'integer', 'min:0', 'max:10'],
            'n_dewasa_tidak_bekerja' => ['nullable', 'integer', 'min:0', 'max:10'],
            'n_dewasa_bekerja_tanggung' => ['nullable', 'integer', 'min:0', 'max:10'],
            'n_ipt' => ['nullable', 'integer', 'min:0', 'max:10'],
            'n_remaja' => ['nullable', 'integer', 'min:0', 'max:10'],
            'n_kanak_rendah' => ['nullable', 'integer', 'min:0', 'max:10'],
            'n_kanak_tadika' => ['nullable', 'integer', 'min:0', 'max:10'],
            'perumahan' => ['required', 'in:sendiri,sewa'],
            'utiliti_input' => ['nullable', 'numeric', 'min:0'],
            'pengangkutan_input' => ['nullable', 'numeric', 'min:0'],
            'perubatan_input' => ['nullable', 'numeric', 'min:0'],
        ]);

        $result = HadAlKifayahService::calculate($validated);

        return view('portal.hadkifayah', compact('user', 'rates', 'result', 'validated'));
    }
}
