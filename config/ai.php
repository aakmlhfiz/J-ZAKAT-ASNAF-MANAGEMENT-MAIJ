<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI provider
    |--------------------------------------------------------------------------
    | One of: 'mock', 'openai', 'gemini', 'ollama'
    | 'mock' is the default — works offline, no API key needed.
    | Real LLM providers are stubbed; set the relevant API key env var to
    | enable them (e.g. AI_OPENAI_API_KEY=sk-...).
    */
    'default' => env('AI_PROVIDER', 'mock'),

    'providers' => [
        'mock' => [
            'driver' => 'mock',
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
    | System prompt used by real LLM providers.
    | The MockProvider ignores this and uses pattern-matching instead.
    */
    'system_prompt' => 'You are the MAIJ Asnaf Assistant, a helpful AI for the Majlis Agama Islam Johor (MAIJ) Smart Asnaf Eligibility System. Respond in Bahasa Malaysia. Use the provided tools (function-calling) to look up applicants, explain eligibility decisions, calculate Had Kifayah (RM 1200 base + RM 350 per dependent + sewa + utiliti), and summarize profiles. Be concise, professional, and always cite specific data points (nama, IC, pendapatan, etc.) when answering.',

    'max_history' => 20,
    'max_message_length' => 1000,
];
