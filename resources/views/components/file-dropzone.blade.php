@props(['name' => 'csv', 'accept' => '.csv', 'label' => null, 'hint' => null, 'progress' => null])
<div x-data="{ dragging: false, fileName: '', progress: {{ (int) ($progress ?? 0) }} }"
     @dragover.prevent="dragging = true"
     @dragleave.prevent="dragging = false"
     @drop.prevent="dragging = false; $event.dataTransfer.files.length && ($refs.input.files = $event.dataTransfer.files, fileName = $event.dataTransfer.files[0].name)"
     class="relative border-2 border-dashed rounded-[20px] p-10 text-center cursor-pointer transition-apple"
     :class="dragging ? 'border-primary bg-primary-soft' : 'border-divider hover:border-primary/50 hover:bg-primary-soft/40'"
     @click="$refs.input.click()">
    <input type="file" name="{{ $name }}" x-ref="input" accept="{{ $accept }}" class="hidden" {{ $attributes }}>
    <div class="w-14 h-14 rounded-[16px] bg-primary-soft mx-auto flex items-center justify-center mb-4 ring-1 ring-primary/15">
        <i class="fa-solid fa-cloud-arrow-up text-primary text-[22px]"></i>
    </div>
    <p class="text-body text-text font-medium" x-text="fileName || @js($label ?? __('imports.dropzone.label'))"></p>
    <p class="text-small text-secondary mt-2">{{ $hint ?? __('imports.dropzone.hint') }}</p>
    <p class="text-eyebrow text-tertiary mt-3">{{ __('imports.dropzone.supported') }}: {{ strtoupper($accept) }}</p>

    <div x-show="progress > 0 && progress < 100" class="mt-5 max-w-xs mx-auto">
        <div class="h-1.5 rounded-pill bg-muted overflow-hidden">
            <div class="h-full bg-primary transition-all duration-300" :style="'width: ' + progress + '%'"></div>
        </div>
        <p class="text-small text-secondary mt-2" x-text="@js(__('imports.dropzone.uploading')) + ' ' + progress + '%'"></p>
    </div>
</div>
