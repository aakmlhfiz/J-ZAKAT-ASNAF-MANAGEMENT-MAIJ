<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\PortalUser;

/**
 * User-scoped assistant for the applicant portal.
 *
 * Deliberately separate from the admin AiService: it never exposes other
 * applicants' data and never invents eligibility decisions. All answers are
 * deterministic, offline-safe, and always include the MAIJ final-decision
 * guardrail for eligibility-related questions.
 */
class PortalAiService
{
    public function chat(PortalUser $user, string $message): array
    {
        $message = mb_strtolower(trim($message));

        if (preg_match('/(bantuan|assistance|aid|type of assistance)/', $message) && preg_match('/(mohon|apply|layak|eligible|boleh|can)/', $message)) {
            return $this->text($this->assistanceTypes());
        }

        if (preg_match('/(dokumen|document)/', $message)) {
            return $this->text($this->requiredDocuments());
        }

        if (preg_match('/(hantar|serah|submit|kemukakan|send).*(permohonan|borang|application|form)/', $message) || preg_match('/(cara mohon|how to apply|how do i apply|how to submit)/', $message)) {
            return $this->text($this->howToSubmit());
        }

        if (preg_match('/(status|semakan|review).*(permohonan|application)/', $message) || preg_match('/(application status|status permohonan)/', $message)) {
            return $this->text($this->applicationStatus($user));
        }

        if (preg_match('/(isi rumah|household|kemas kini|update).*(maklumat|information|isi rumah|household|rumah)/', $message)) {
            return $this->text($this->updateHousehold());
        }

        if (preg_match('/(dokumen|document).*(ditolak|reject|rejected)|(?:ditolak|rejected).*(dokumen|document)/', $message) || preg_match('/(dokumen ditolak|rejected document|document rejected)/', $message)) {
            return $this->text($this->rejectedDocument());
        }

        return $this->suggestions();
    }

    private function text(string $reply): array
    {
        return ['reply' => $reply, 'data' => ['type' => 'text']];
    }

    private function suggestions(): array
    {
        $items = [
            ['key' => 'types'],
            ['key' => 'documents_item'],
            ['key' => 'submit'],
            ['key' => 'status'],
            ['key' => 'household'],
            ['key' => 'rejected'],
        ];

        $suggestions = array_map(fn ($item, $idx) => [
            'id' => 's'.$idx,
            'icon' => 'fa-solid fa-circle-question',
            'label' => __('ai.portal.suggestions.'.$item['key'].'.label'),
            'prompt' => __('ai.portal.suggestions.'.$item['key'].'.prompt'),
        ], $items, array_keys($items));

        return [
            'reply' => __('ai.portal.suggestions_reply'),
            'data' => ['type' => 'suggestions', 'suggestions' => $suggestions],
        ];
    }

    private function assistanceTypes(): string
    {
        $labels = __('ai.portal.assistance_types');
        $list = collect(AssistanceHistory::jenisBantuanOptions())
            ->map(fn ($label) => '• '.($labels[$label] ?? $label))
            ->implode("\n");

        return __('ai.portal.assistance_reply', [
            'list' => $list,
            'guardrail' => $this->guardrail(),
        ]);
    }

    private function requiredDocuments(): string
    {
        return __('ai.portal.documents_reply', [
            'guardrail' => $this->guardrail(),
        ]);
    }

    private function howToSubmit(): string
    {
        return __('ai.portal.how_to_submit_reply');
    }

    private function applicationStatus(PortalUser $user): string
    {
        $apps = $user->applications()->orderByDesc('id')->get();

        if ($apps->isEmpty()) {
            return __('ai.portal.no_applications');
        }

        $lines = [__('ai.portal.status_intro')];

        foreach ($apps->take(5) as $app) {
            $status = __('ai.portal.application_statuses.'.$app->status);

            if (! is_string($status) || str_starts_with($status, 'ai.portal.')) {
                $status = ucfirst((string) $app->status);
            }

            $lines[] = __('ai.portal.status_line', [
                'no' => $app->application_no,
                'status' => $status,
            ]);
        }

        $lines[] = '';
        $lines[] = __('ai.portal.status_footer', ['guardrail' => $this->guardrail()]);

        return implode("\n", $lines);
    }

    private function updateHousehold(): string
    {
        return __('ai.portal.update_household');
    }

    private function rejectedDocument(): string
    {
        return __('ai.portal.rejected_document');
    }

    private function guardrail(): string
    {
        return __('ai.portal.guardrail');
    }
}
