@props(['name' => 'toggle', 'checked' => false, 'label' => null, 'description' => null, 'onValue' => '1', 'offValue' => '0'])
<label class="flex items-center gap-3 cursor-pointer select-none">
    <span class="relative inline-flex">
        <input type="hidden" name="{{ $name }}" value="{{ $offValue }}">
        <input type="checkbox" name="{{ $name }}" value="{{ $onValue }}" @checked($checked) class="peer sr-only">
        <span class="w-10 h-6 rounded-pill bg-divider transition-apple peer-checked:bg-primary"></span>
        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-apple peer-checked:translate-x-4"></span>
    </span>
    <span class="min-w-0 flex-1">
        @if($label)<span class="block text-body text-text font-medium">{{ $label }}</span>@endif
        @if($description)<span class="block text-small text-secondary mt-0.5">{{ $description }}</span>@endif
    </span>
</label>
