@props(['title' => '', 'description' => null, 'id' => null, 'icon' => null])
<header {{ $attributes->merge(['class' => 'mb-5']) }} @if($id) id="{{ $id }}" @endif>
    @if($title)
        <h2 class="text-h3 text-text tracking-tight flex items-center gap-2">
            @if($icon)<x-icon :name="$icon" :size="17" class="text-tertiary" />@endif
            {{ $title }}
        </h2>
    @endif
    @if($description)<p class="text-small text-secondary mt-1.5 max-w-prose">{{ $description }}</p>@endif
</header>
