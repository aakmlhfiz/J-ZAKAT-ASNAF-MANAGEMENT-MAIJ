<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PortalAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function page(): View
    {
        return view('portal.ai');
    }

    public function chat(Request $request, PortalAiService $assistant): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = auth('portal')->user();

        $result = $assistant->chat($user, $data['message']);

        return response()->json($result);
    }
}
