<form @submit.prevent="send()" class="border-t border-divider p-3 md:p-4 bg-card flex items-end gap-3">
    <div class="flex-1 relative">
        <textarea x-model="draft"
            @keydown.enter.prevent="if (!event.shiftKey) send()"
            rows="1"
            placeholder="{{ __('ai.input.placeholder') }}"
            class="input-base resize-none pr-11 py-3 max-h-32"></textarea>
        <i class="fa-solid fa-message text-tertiary absolute right-3.5 bottom-3 text-[13px] pointer-events-none"></i>
    </div>
    <button type="submit" :disabled="!draft.trim() || loading" class="btn btn-primary btn-icon btn-lg shrink-0" aria-label="{{ __('ai.input.send') }}">
        <i class="fa-solid fa-paper-plane text-[14px]"></i>
    </button>
</form>
