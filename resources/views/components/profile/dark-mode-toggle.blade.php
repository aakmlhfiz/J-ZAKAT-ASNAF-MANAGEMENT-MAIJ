@props(['current' => 'system'])
<div x-data="themeCard()" class="grid grid-cols-3 gap-3">
    <button @click="setTheme('light')" type="button"
        class="theme-card surface-flat p-3 rounded-[14px] flex flex-col items-center gap-2 hover:shadow-md transition-apple"
        :class="{ 'selected': selected === 'light' }">
        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
            <i class="fa-solid fa-sun text-warning text-[14px]"></i>
        </div>
        <span class="text-small text-text font-semibold">Cerah</span>
    </button>
    <button @click="setTheme('dark')" type="button"
        class="theme-card surface-flat p-3 rounded-[14px] flex flex-col items-center gap-2 hover:shadow-md transition-apple"
        :class="{ 'selected': selected === 'dark' }">
        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center">
            <i class="fa-solid fa-moon text-slate-200 text-[14px]"></i>
        </div>
        <span class="text-small text-text font-semibold">Gelap</span>
    </button>
    <button @click="setTheme('system')" type="button"
        class="theme-card surface-flat p-3 rounded-[14px] flex flex-col items-center gap-2 hover:shadow-md transition-apple"
        :class="{ 'selected': selected === 'system' }">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-white to-slate-400 flex items-center justify-center">
            <i class="fa-solid fa-display text-slate-600 text-[14px]"></i>
        </div>
        <span class="text-small text-text font-semibold">Sistem</span>
    </button>
</div>
