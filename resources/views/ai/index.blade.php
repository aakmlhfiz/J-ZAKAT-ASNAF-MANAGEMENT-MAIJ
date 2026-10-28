@extends('layouts.app', [
    'title' => __('ai.page.title'),
    'subtitle' => __('ai.page.subtitle'),
])
@section('title', __('ai.page.tab_title'))
@section('content')
<div x-data="aiChatWidget" class="max-w-3xl mx-auto">
    <div class="mb-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-[14px] text-white flex items-center justify-center font-bold text-[16px] shrink-0" style="background: var(--gradient-royal);">UD</div>
        <div class="min-w-0">
            <h1 class="text-title text-text">Ustaz Danial</h1>
            <p class="text-small text-secondary">{{ __('ai.page.role') }}</p>
        </div>
        <button @click="clear()" type="button" class="ml-auto w-9 h-9 rounded-[10px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-fast shrink-0" aria-label="{{ __('ai.page.clear_history') }}">
            <x-icon name="trash" :size="16" />
        </button>
    </div>

    <x-app-card class="p-0 overflow-hidden" :hover="false">
        <div x-ref="messages" class="ai-messages flex-1 overflow-y-auto px-5 md:px-6 py-6 space-y-4 bg-bg min-h-[420px] max-h-[60vh]">
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex items-start gap-2'">
                    <div x-show="msg.role === 'ai'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-[11px] shrink-0" style="background: var(--gradient-royal);">UD</div>
                    <div :class="msg.role === 'user' ? 'bg-primary text-white rounded-[20px] rounded-br-md max-w-[80%] px-4 py-3 shadow-sm' : 'bg-card border border-divider rounded-[20px] rounded-bl-md max-w-[88%] px-4 py-3 shadow-card'">
                        <div x-html="msg.role === 'ai' ? renderMarkdown(msg.text) : msg.text" class="text-small leading-relaxed"></div>
                        <div x-show="msg.role === 'ai' && msg.data && msg.data.type" x-html="renderData(msg.data)" class="mt-3"></div>
                    </div>
                </div>
            </template>

            <div x-show="messages.length === 0" class="text-center py-12">
                <div class="w-16 h-16 rounded-[20px] mx-auto flex items-center justify-center mb-4" style="background: var(--gradient-royal);">
                    <span class="text-white font-bold text-[22px]">UD</span>
                </div>
                <h3 class="text-h3 text-text">{{ __('ai.page.empty_title') }}</h3>
                <p class="text-body text-secondary mt-2 max-w-sm mx-auto">{{ __('ai.page.empty_body') }}</p>
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-md mx-auto">
                    <button @click="draft = @js(__('ai.suggestions.prompt_eligibility_stats')); $dispatch('ai-send')" type="button" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <x-icon name="chart-pie" :size="15" class="text-primary" />
                        <p class="text-small font-medium text-text mt-1.5">{{ __('ai.suggestions.eligibility_stats') }}</p>
                    </button>
                    <button @click="draft = @js(__('ai.suggestions.prompt_generate_assessment')); $dispatch('ai-send')" type="button" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <x-icon name="activity" :size="15" class="text-primary" />
                        <p class="text-small font-medium text-text mt-1.5">{{ __('ai.suggestions.generate_assessment') }}</p>
                    </button>
                    <button @click="draft = @js(__('ai.suggestions.prompt_search_applicant')); $dispatch('ai-send')" type="button" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <x-icon name="search" :size="15" class="text-primary" />
                        <p class="text-small font-medium text-text mt-1.5">{{ __('ai.suggestions.search_applicant') }}</p>
                    </button>
                    <button @click="draft = @js(__('ai.suggestions.prompt_kifayah_rate')); $dispatch('ai-send')" type="button" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <x-icon name="calculator" :size="15" class="text-primary" />
                        <p class="text-small font-medium text-text mt-1.5">{{ __('ai.suggestions.kifayah_rate') }}</p>
                    </button>
                </div>
            </div>

            <div x-show="loading" class="flex justify-start">
                <div class="bg-card border border-divider rounded-[20px] rounded-bl-md px-4 py-3 shadow-card">
                    <span class="inline-flex items-center gap-1.5 align-middle">
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                    </span>
                </div>
            </div>
        </div>

        <x-ai-input />
    </x-app-card>

    <p class="text-[11px] text-tertiary mt-3 text-center">{{ __('ai.page.disclaimer') }}</p>
</div>
@endsection
