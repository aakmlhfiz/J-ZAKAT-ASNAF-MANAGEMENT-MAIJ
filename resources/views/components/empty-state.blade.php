@props(['icon' => 'folder-open', 'title' => null, 'description' => null])
<div class="text-center py-14 px-6">
    <div class="w-16 h-16 rounded-[12px] bg-primary-soft mx-auto flex items-center justify-center mb-5 ring-1 ring-primary/10 text-primary">
        <x-icon :name="$icon" :size="26" />
    </div>
    <p class="text-h3 text-text">{{ $title ?? __('ui.empty.title') }}</p>
    @if($description)<p class="text-body text-secondary mt-2 max-w-sm mx-auto">{{ $description }}</p>@endif
    @isset($action)<div class="mt-5">{{ $action }}</div>@endisset
</div>
