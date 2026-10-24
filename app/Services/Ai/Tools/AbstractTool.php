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
}
