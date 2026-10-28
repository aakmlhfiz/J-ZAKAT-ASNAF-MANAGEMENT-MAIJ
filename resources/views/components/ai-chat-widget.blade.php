@php
    $aiStrings = [
        'locale' => app()->getLocale(),
        'error' => __('ai.errors.connection'),
        'no_reply' => __('ai.errors.no_reply'),
        'card_stats' => __('ai.card.stats'),
        'card_profile' => __('ai.card.profile'),
        'card_explanation' => __('ai.card.explanation'),
        'card_result' => __('ai.card.result'),
        'card_search_results' => __('ai.card.search_results'),
        'card_suggestions' => __('ai.card.suggestions'),
        'card_ai_score' => __('ai.card.ai_score'),
        'status_layak' => __('common.status.layak'),
        'status_tidak_layak' => __('common.status.tidak_layak'),
        'status_dalam_semakan' => __('common.status.dalam_semakan'),
        'providers' => [
            'mock' => __('ai.providers.labels.mock'),
            'groq' => __('ai.providers.labels.groq'),
            'openai' => __('ai.providers.labels.openai'),
            'gemini' => __('ai.providers.labels.gemini'),
            'ollama' => __('ai.providers.labels.ollama'),
        ],
    ];
@endphp
<div x-data="aiChatWidget" class="fixed bottom-5 right-5 z-40">
    <button @click="open = !open"
            :aria-expanded="open"
            aria-label="{{ __('ai.widget.open') }}"
            class="relative w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-[15px] tracking-tight shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-apple"
            style="background: var(--gradient-royal); box-shadow: 0 12px 28px -10px rgba(37,99,235,0.7);">
        <span>UD</span>
        <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-card flex items-center justify-center ring-2 ring-white shadow-sm">
            <x-icon name="message-circle" :size="10" class="text-primary" />
        </span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 bottom-full mb-3 w-[380px] max-w-[calc(100vw-2rem)] max-sm:fixed max-sm:inset-3 max-sm:w-auto max-sm:bottom-auto max-sm:mb-0 h-[560px] max-h-[80vh] max-sm:h-[calc(100vh-7rem)] surface-elevated flex flex-col z-50 overflow-hidden">
        <div class="px-4 py-3 flex items-center gap-3 text-white" style="background: var(--gradient-brand);">
            <div class="w-9 h-9 rounded-[10px] bg-white/20 backdrop-blur flex items-center justify-center text-white font-bold text-[13px]">UD</div>
            <div class="flex-1 min-w-0">
                <p class="text-small font-semibold leading-none">Ustaz Danial</p>
                <p class="text-[10px] opacity-90 mt-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse-soft"></span>
                    <span x-text="providerLabel()"></span> · {{ __('ai.widget.online') }}
                </p>
            </div>
            <button @click="open = false" class="w-8 h-8 rounded-[8px] hover:bg-white/15 transition-fast flex items-center justify-center text-white/85" aria-label="{{ __('ai.widget.close') }}">
                <x-icon name="x" :size="15" />
            </button>
        </div>

        <div x-ref="messages" class="ai-messages flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-bg">
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex items-start gap-2'">
                    <div x-show="msg.role === 'ai'" class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold text-[9px] shrink-0" style="background: var(--gradient-royal);">UD</div>
                    <div :class="msg.role === 'user' ? 'bg-primary text-white rounded-[18px] rounded-br-md max-w-[85%] px-3.5 py-2.5' : 'bg-card border border-divider rounded-[18px] rounded-bl-md max-w-[92%] px-3.5 py-2.5 shadow-sm'">
                        <div x-html="msg.role === 'ai' ? renderMarkdown(msg.text) : msg.text" class="text-small leading-relaxed"></div>
                        <div x-show="msg.role === 'ai' && msg.data && msg.data.type" x-html="renderData(msg.data)" class="mt-2.5"></div>
                    </div>
                </div>
            </template>
            <div x-show="messages.length === 0" class="text-center py-6 px-3">
                <div class="w-12 h-12 rounded-[14px] mx-auto flex items-center justify-center mb-3" style="background: var(--gradient-royal);">
                    <span class="text-white font-bold text-[16px]">UD</span>
                </div>
                <p class="text-body text-text font-semibold">{{ __('ai.widget.greeting') }}</p>
                <p class="text-small text-secondary mt-1">{{ __('ai.widget.greeting_body') }}</p>
            </div>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-card border border-divider rounded-[18px] rounded-bl-md px-3.5 py-2.5 shadow-sm">
                    <span class="inline-flex items-center gap-1 align-middle">
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="border-t border-divider p-3 bg-card">
            <form @submit.prevent="send()" class="flex items-center gap-2">
                <input x-model="draft" type="text" placeholder="{{ __('ai.widget.placeholder') }}" class="input-base flex-1 py-2.5 text-[13px]">
                <button type="submit" :disabled="!draft.trim() || loading" class="btn btn-primary btn-icon btn-sm" aria-label="{{ __('ai.widget.send') }}">
                    <x-icon name="send" :size="15" />
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    window.aiChatStrings = Object.assign(window.aiChatStrings || {}, @json($aiStrings));
</script>
