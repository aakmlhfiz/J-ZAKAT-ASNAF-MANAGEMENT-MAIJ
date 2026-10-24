<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $status = [
            'db' => 'Aktif',
            'db_driver' => DB::connection()->getDriverName(),
            'firebase' => config('services.firebase.project_id') ? 'Aktif' : 'Tidak Dikonfigurasi',
            'ai_provider' => config('ai.default', 'mock'),
            'cache' => config('cache.default') === 'database' ? 'Pangkalan Data' : ucfirst(config('cache.default', 'fail')),
        ];

        return view('settings', compact('status'));
    }
}
