@props(['enabled' => false])
<div x-data="twoFactorFlow()"
    data-enabled="{{ $enabled ? '1' : '0' }}"
    data-enable-url="{{ route('profile.2fa.enable') }}"
    data-verify-url="{{ route('profile.2fa.verify') }}"
    data-disable-url="{{ route('profile.2fa.disable') }}"
    class="space-y-4">

    {{-- Idle: Not enabled --}}
    <div x-show="state === 'idle' && !isEnabled" x-cloak>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-h3 text-text">{{ __('profile.security.two_factor.title') }}</h3>
                <p class="text-small text-secondary mt-1">{{ __('profile.security.two_factor.idle_desc') }}</p>
            </div>
            <button @click="startEnrollment()" type="button"
                class="btn btn-primary btn-sm shrink-0">
                <i class="fa-solid fa-shield-halved text-[12px]"></i>
                {{ __('profile.security.two_factor.enable') }}
            </button>
        </div>
    </div>

    {{-- Enrolling: Show QR + code input --}}
    <div x-show="state === 'enrolling'" x-cloak>
        <h3 class="text-h3 text-text mb-1">{{ __('profile.security.two_factor.setup_title') }}</h3>
        <p class="text-small text-secondary mb-4">{{ __('profile.security.two_factor.setup_desc') }}</p>

        <div class="flex flex-col sm:flex-row items-center gap-5 mb-4">
            <canvas x-ref="qrCanvas" class="shrink-0 bg-white p-3 rounded-[16px] shadow-sm" width="200" height="200"></canvas>
            <div class="flex-1 min-w-0 space-y-2">
                <p class="text-eyebrow text-tertiary">{{ __('profile.security.two_factor.manual_code') }}</p>
                <code class="block text-[13px] font-mono break-all bg-muted p-2 rounded-[8px] text-text" x-text="secret"></code>
                <p class="text-small text-secondary">{{ __('profile.security.two_factor.manual_hint') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 max-w-xs">
            <input type="text" x-model="code" maxlength="6" placeholder="000000" @input="error = ''"
                class="input-base text-center text-[18px] tracking-[8px] font-mono w-full sm:w-40" autocomplete="one-time-code" inputmode="numeric">
            <button @click="verifyEnrollment()" :disabled="loading || code.length !== 6"
                class="btn btn-primary btn-sm" x-text="loading ? @js(__('profile.security.two_factor.verifying')) : @js(__('profile.security.two_factor.verify'))"></button>
        </div>
        <p x-show="error" x-text="error" class="text-small text-danger mt-2"></p>
        <button @click="state = 'idle'" class="btn btn-ghost btn-sm mt-3">{{ __('profile.security.two_factor.cancel') }}</button>
    </div>

    {{-- Enabled: Show status + disable button --}}
    <div x-show="isEnabled && state !== 'enrolling'" x-cloak>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-h3 text-text flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-success text-[14px]"></i>
                    {{ __('profile.security.two_factor.title') }}
                </h3>
                <p class="text-small text-success font-semibold mt-1">{{ __('profile.security.two_factor.enabled_desc') }}</p>
            </div>
            <button @click="disable2FA()" type="button"
                class="btn btn-destructive btn-sm shrink-0">
                {{ __('profile.security.two_factor.disable') }}
            </button>
        </div>

        {{-- Password confirmation prompt --}}
        <div x-show="showPasswordPrompt" x-cloak class="mt-4 p-4 soft-panel">
            <p class="text-small text-text font-medium mb-3">{{ __('profile.security.two_factor.password_prompt') }}</p>
            <div class="flex items-center gap-2">
                <input type="password" x-model="disablePassword" placeholder="{{ __('profile.security.two_factor.password_placeholder') }}"
                    class="input-base w-full sm:w-56" @keydown.enter="confirmDisable2FA()">
                <button @click="confirmDisable2FA()" :disabled="loading || !disablePassword"
                    class="btn btn-destructive btn-sm" x-text="loading ? @js(__('profile.security.two_factor.disabling')) : @js(__('profile.security.two_factor.verify'))"></button>
                <button @click="cancelDisable2FA()" :disabled="loading"
                    class="btn btn-ghost btn-sm">{{ __('profile.security.two_factor.cancel') }}</button>
            </div>
            <p x-show="error" x-text="error" class="text-small text-danger mt-2"></p>
        </div>
    </div>

    {{-- Loading spinner --}}
    <div x-show="loading && state === 'idle'" x-cloak class="flex items-center gap-2 text-secondary">
        <i class="fa-solid fa-spinner fa-spin"></i> {{ __('profile.security.two_factor.processing') }}
    </div>
</div>
