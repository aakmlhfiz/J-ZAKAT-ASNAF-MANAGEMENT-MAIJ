<?php

namespace App\Services\Ai\Contracts;

interface AiProvider
{
    /**
     * Process a user message and return an AI response.
     *
     * @param  string  $message  The raw user message
     * @param  array  $history  Recent chat history (array of {role, content} pairs)
     * @return array  { reply: string, data?: array }
     */
    public function chat(string $message, array $history = []): array;

    /**
     * Unique identifier for this provider (matches config('ai.default')).
     */
    public function name(): string;
}
