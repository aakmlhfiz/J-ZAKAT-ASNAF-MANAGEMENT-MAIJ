<?php

namespace App\Services\Ai\Tools;

abstract class AbstractTool
{
    /**
     * Pattern-match the user's message to decide if this tool applies.
     * Should be fast and side-effect free.
     */
    abstract public function matches(string $message): bool;

    /**
     * Produce the structured response for the matched message.
     * Return shape: { reply: string, data: array }
     */
    abstract public function handle(string $message): array;

    /**
     * Short, user-friendly label for this tool (used in suggestion chips).
     */
    abstract public function label(): string;

    /**
     * FontAwesome icon class (without the 'fa-solid ' prefix).
     */
    abstract public function icon(): string;

    /**
     * Localized display label for a stored eligibility status.
     * DB values stay unchanged ('Layak', 'Tidak Layak', 'Dalam Semakan').
     */
    protected function statusLabel(?string $status): string
    {
        return match ((string) $status) {
            'Layak' => __('common.status.layak'),
            'Tidak Layak' => __('common.status.tidak_layak'),
            'Dalam Semakan' => __('common.status.dalam_semakan'),
            default => (string) $status,
        };
    }
}
