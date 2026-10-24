<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AiController extends Controller
{
    public function __construct(private AiService $ai) {}

    /**
     * Full chat page (for direct linking + mobile).
     */
    public function page(): View
    {
        return view('ai.index');
    }

    /**
     * POST /ai/chat — receives { message, history? }, returns { reply, data, provider }.
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'message' => 'required|string|max:2000',
                'history' => 'sometimes|array',
                'history.*.role' => 'sometimes|string|in:user,assistant,system',
                'history.*.content' => 'sometimes|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'reply' => 'Sila taip soalan yang sah.',
                'data' => ['type' => 'text', 'error' => 'validation'],
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $result = $this->ai->chat($data['message'], $data['history'] ?? []);

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('AI chat failed', ['error' => $e->getMessage()]);

            return response()->json([
                'reply' => 'Maaf, berlaku ralat semasa memproses permintaan AI. Sila cuba lagi sebentar.',
                'data' => ['type' => 'text', 'error' => 'server'],
            ], 500);
        }
    }

    /**
     * POST /ai/clear — placeholder for client-side session-only clear.
     * Server has no state to clear, but we keep the route for symmetry.
     */
    public function clear(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }
}
