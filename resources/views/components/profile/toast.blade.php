<div x-data="toastManager()" @toast.window="add($event.detail)" class="profile-toast-container" x-show="toasts.length > 0" x-cloak>
    <template x-for="(t, idx) in toasts" x-bind:key="t.id">
        <div x-bind:class="'profile-toast ' + (t.type === 'error' ? 'toast-error' : t.type === 'warning' ? 'toast-warning' : '')"
            x-show="!t.removing"
            x-transition:leave="profile-toast.removing"
            class="profile-fade-in">
            <div class="w-8 h-8 rounded-[10px] shrink-0 flex items-center justify-center"
                x-bind:class="t.type === 'error' ? 'bg-danger-soft text-danger' : t.type === 'warning' ? 'bg-warning-soft text-warning' : 'bg-success-soft text-success'">
                <i x-bind:class="t.type === 'error' ? 'fa-solid fa-circle-exclamation' : t.type === 'warning' ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-circle-check'" class="text-[14px]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-small text-text font-semibold" x-text="t.title || 'Berjaya'"></p>
                <p class="text-small text-secondary mt-0.5" x-text="t.message"></p>
            </div>
            <button @click="dismiss(t.id)" class="text-tertiary hover:text-text transition-fast shrink-0">
                <i class="fa-solid fa-xmark text-[13px]"></i>
            </button>
        </div>
    </template>
</div>
