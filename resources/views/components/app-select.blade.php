@props(['label', 'name', 'required' => false, 'placeholder' => '', 'hint' => null, 'error' => null, 'value' => null])
@php $invalid = (bool) ($error ?? ($errors && $errors->has($name))); @endphp
<div>
    <label for="{{ $name }}" class="block text-small font-medium text-text mb-1.5">
        {{ $label }}
        @if($required) <span class="text-danger">*</span> @endif
    </label>
    <div class="relative">
        <select
            name="{{ $name }}" id="{{ $name }}"
            @if($required) required @endif
            @if($invalid) aria-invalid="true" @endif
            {{ $attributes->merge(['class' => 'input-base appearance-none pr-10' . ($invalid ? ' is-invalid' : '')]) }}
        >
            @if($placeholder) <option value="">{{ $placeholder }}</option> @endif
            {{ $slot }}
        </select>
        <x-icon name="chevron-down" :size="14" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
    </div>
    @if($hint && !$invalid) <p class="text-small text-secondary mt-1.5">{{ $hint }}</p> @endif
    @if($invalid) <p class="text-small text-danger mt-1.5 flex items-center gap-1.5"><x-icon name="circle-alert" :size="13" />{{ $error ?? $errors->first($name) }}</p> @endif
</div>
