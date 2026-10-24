@props(['label', 'name', 'rows' => 4, 'required' => false, 'placeholder' => '', 'hint' => null, 'error' => null, 'value' => null, 'icon' => null])
@php $invalid = (bool) ($error ?? ($errors && $errors->has($name))); @endphp
<div>
    <label for="{{ $name }}" class="block text-small font-medium text-text mb-1.5">
        {{ $label }}
        @if($required) <span class="text-danger">*</span> @endif
    </label>
    <textarea
        name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($invalid) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'input-base resize-y' . ($invalid ? ' is-invalid' : '')]) }}
    >{{ $value ?? old($name) }}</textarea>
    @if($hint && !$invalid) <p class="text-small text-secondary mt-1.5">{{ $hint }}</p> @endif
    @if($invalid) <p class="text-small text-danger mt-1.5 flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error ?? $errors->first($name) }}</p> @endif
</div>
