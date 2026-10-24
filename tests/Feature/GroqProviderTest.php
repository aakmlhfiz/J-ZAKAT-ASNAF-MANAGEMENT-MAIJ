<?php

namespace Tests\Feature;

use App\Services\Ai\AiService;
use App\Services\Ai\Providers\GroqProvider;
use App\Services\Ai\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_groq_is_used_when_configured(): void
    {
        config([
            'ai.default' => 'groq',
            'ai.providers.groq.api_key' => 'test-groq-key',
        ]);

        $service = new AiService;

        $this->assertSame('groq', $service->name());
    }

    public function test_groq_falls_back_to_mock_without_api_key(): void
    {
        config([
            'ai.default' => 'groq',
            'ai.providers.groq.api_key' => null,
        ]);

        $service = new AiService;

        $this->assertSame('mock', $service->name());
    }

    public function test_groq_returns_a_normal_chat_reply(): void
    {
        config([
            'ai.providers.groq.api_key' => 'test-groq-key',
            'ai.providers.groq.base' => 'https://api.groq.com/openai/v1',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Saya boleh membantu anda mengurus data asnaf.',
                    ],
                ]],
            ]),
        ]);

        $result = (new GroqProvider)->chat('Boleh bantu saya?');

        $this->assertSame('Saya boleh membantu anda mengurus data asnaf.', $result['reply']);
        $this->assertSame('text', $result['data']['type']);
        Http::assertSentCount(1);
    }

    public function test_groq_executes_a_tool_call_and_returns_the_final_reply(): void
    {
        config([
            'ai.providers.groq.api_key' => 'test-groq-key',
            'ai.providers.groq.base' => 'https://api.groq.com/openai/v1',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push([
                    'choices' => [[
                        'message' => [
                            'role' => 'assistant',
                            'content' => null,
                            'tool_calls' => [[
                                'id' => 'call_1',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_dashboard_stats',
                                    'arguments' => '{}',
                                ],
                            ]],
                        ],
                    ]],
                ])
                ->push([
                    'choices' => [[
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Berdasarkan data, belum ada pemohon direkodkan.',
                        ],
                    ]],
                ]),
        ]);

        $result = (new GroqProvider)->chat('Berapa jumlah pemohon?');

        $this->assertSame('Berdasarkan data, belum ada pemohon direkodkan.', $result['reply']);
        $this->assertSame('stats', $result['data']['type']);

        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            $messages = $request['messages'] ?? [];

            return collect($messages)->contains(
                fn ($message) => ($message['role'] ?? null) === 'tool'
                    && ($message['name'] ?? null) === 'get_dashboard_stats'
                    && str_contains((string) ($message['content'] ?? ''), 'Statistik Pemohon')
            );
        });
    }

    public function test_tool_registry_exposes_all_assistant_tools(): void
    {
        $definitions = (new ToolRegistry)->definitions();

        $names = collect($definitions)->pluck('function.name')->all();

        $this->assertSame([
            'get_applicant',
            'search_applicants',
            'get_dashboard_stats',
            'explain_eligibility',
            'calculate_kifayah',
        ], $names);
        $this->assertNotFalse(json_encode($definitions));
    }

    public function test_calculate_kifayah_requires_household_data(): void
    {
        $result = (new ToolRegistry)->execute('calculate_kifayah', []);

        $this->assertSame('tool_input', $result['data']['error']);
    }
}
