@props(['current' => 'system'])
<div x-data="themeCard()" class="grid grid-cols-2 gap-3">
    <button @click="setTheme('light')" type="button"
        class="theme-card surface-flat p-3 rounded-[8px] flex flex-col items-center gap-2 hover:shadow-md transition-apple"
        :class="{ 'selected': selected === 'light' }">
        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
            <i class="fa-solid fa-sun text-warning text-[14px]"></i>
        </div>
        <span class="text-small text-text font-semibold">{{ __('profile.settings.theme_light') }}</span>
    </button>
    <button @click="setTheme('dark')" type="button"
        class="theme-card surface-flat p-3 rounded-[8px] flex flex-col items-center gap-2 hover:shadow-md transition-apple"
        :class="{ 'selected': selected === 'dark' }">
        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center">
            <i class="fa-solid fa-moon text-slate-200 text-[14px]"></i>
        </div>
        <span class="text-small text-text font-semibold">{{ __('profile.settings.theme_dark') }}</span>
    </button>
</div>
