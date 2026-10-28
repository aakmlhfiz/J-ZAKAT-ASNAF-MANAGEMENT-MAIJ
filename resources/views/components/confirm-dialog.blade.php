<div x-data="{ open: false, title: '', message: '', confirmText: @js(__('common.actions.continue')), cancelText: @js(__('common.actions.cancel')), variant: 'primary', action: null }"
     x-show="open"
     @open-confirm.window="
        title = $event.detail.title;
        message = $event.detail.message;
        confirmText = $event.detail.confirmText || @js(__('common.actions.continue'));
        cancelText = $event.detail.cancelText || @js(__('common.actions.cancel'));
        variant = $event.detail.variant || 'primary';
        action = $event.detail.action || null;
        open = true;
        document.body.classList.add('overflow-hidden');
     "
     @keydown.escape.window="if (open) { open = false; document.body.classList.remove('overflow-hidden'); }"
     @close-confirm.window="open = false; document.body.classList.remove('overflow-hidden');"
     @confirmed.window="$dispatch('close-confirm', { action: action }); action = null;"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4"
     style="display: none;"
     role="dialog"
     aria-modal="true">

    <div x-show="open" x-transition.opacity class="absolute inset-0 -z-10 bg-text/40 backdrop-blur-sm"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-card rounded-[20px] p-7 max-w-md w-full shadow-xl">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-[14px] flex items-center justify-center shrink-0"
                 :class="variant === 'destructive' ? 'bg-danger-soft text-danger' : (variant === 'warning' ? 'bg-warning-soft text-warning' : 'bg-primary-soft text-primary')">
                <i class="fa-solid text-[18px]" :class="variant === 'destructive' ? 'fa-triangle-exclamation' : 'fa-circle-info'"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-h3 text-text" x-text="title"></h2>
                <p class="text-body text-secondary mt-2" x-text="message"></p>
                <div class="flex items-center gap-3 mt-6">
                    <button @click="open = false; document.body.classList.remove('overflow-hidden')"
                            class="btn btn-secondary btn-md flex-1 sm:flex-none"
                            x-text="cancelText"></button>
                    <button @click="$dispatch('confirmed')"
                            :class="'btn btn-md flex-1 sm:flex-none btn-' + (variant === 'destructive' ? 'destructive' : (variant === 'warning' ? 'secondary' : 'primary'))"
                            x-text="confirmText"></button>
                </div>
            </div>
        </div>
    </div>
</div>
