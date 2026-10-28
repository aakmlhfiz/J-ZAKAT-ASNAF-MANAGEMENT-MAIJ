<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI provider
    |--------------------------------------------------------------------------
    | One of: 'mock', 'groq', 'openai', 'gemini', 'ollama'
    | 'mock' is the default — works offline, no API key needed.
    | Groq is implemented with OpenAI-compatible function calling.
    | OpenAI, Gemini and Ollama remain stubs until implemented.
    */
    'default' => env('AI_PROVIDER', 'mock'),

    'providers' => [
        'mock' => [
            'driver' => 'mock',
        ],
        'groq' => [
            'driver' => 'groq',
            'api_key' => env('AI_GROQ_API_KEY'),
            'model' => env('AI_GROQ_MODEL', 'openai/gpt-oss-120b'),
            'base' => env('AI_GROQ_BASE', 'https://api.groq.com/openai/v1'),
            'timeout' => (int) env('AI_GROQ_TIMEOUT', 30),
            'max_tokens' => (int) env('AI_GROQ_MAX_TOKENS', 900),
        ],
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('AI_OPENAI_API_KEY'),
            'model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
            'base' => env('AI_OPENAI_BASE', 'https://api.openai.com/v1'),
        ],
        'gemini' => [
            'driver' => 'gemini',
            'api_key' => env('AI_GEMINI_API_KEY'),
            'model' => env('AI_GEMINI_MODEL', 'gemini-1.5-flash'),
        ],
        'ollama' => [
            'driver' => 'ollama',
            'host' => env('AI_OLLAMA_HOST', 'http://127.0.0.1:11434'),
            'model' => env('AI_OLLAMA_MODEL', 'llama3.1'),
        ],
    ],

    /*
    | Fallback system prompt for real LLM providers. The active prompt is
    | resolved per request from lang/{locale}/ai.php (`ai.system_prompt`) so
    | the assistant answers in the current interface language. This value is
    | only used when that translation is missing.
    | The MockProvider ignores this and uses pattern-matching instead.
    */
    'system_prompt' => 'You are the J-ZAKAT Asnaf Assistant for Majlis Agama Islam Johor (MAIJ). Respond in English. Use the provided tools to look up applicants, explain eligibility decisions, calculate Had Kifayah, and summarize profiles. Never invent applicant data or calculation rates: always call the relevant tool first. If a tool returns no record, say so clearly. Be concise, professional, protect personal data, and cite the specific data returned by the tools.',

    'max_history' => 20,
    'max_message_length' => 1000,
];
