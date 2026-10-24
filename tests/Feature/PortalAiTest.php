<?php

namespace Tests\Feature;

use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_page_requires_authentication(): void
    {
        $this->get(route('user.ai.page'))->assertRedirect(route('user.login'));
    }

    public function test_ai_chat_answers_assistance_question_with_guardrail(): void
    {
        $user = PortalUser::factory()->create();

        $this->actingAs($user, 'portal')->postJson(route('user.ai.chat'), [
            'message' => 'Apakah bantuan yang boleh saya mohon?',
        ])
            ->assertOk()
            ->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'Sara Hidup'))
            ->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'MAIJ'));
    }

    public function test_ai_chat_answers_status_question(): void
    {
        $user = PortalUser::factory()->create();

        $this->actingAs($user, 'portal')->postJson(route('user.ai.chat'), [
            'message' => 'Apakah status permohonan saya?',
        ])
            ->assertOk()
            ->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'permohonan'));
    }

    public function test_ai_chat_validates_message(): void
    {
        $user = PortalUser::factory()->create();

        $this->actingAs($user, 'portal')->postJson(route('user.ai.chat'), [
            'message' => '',
        ])->assertStatus(422);
    }
}
