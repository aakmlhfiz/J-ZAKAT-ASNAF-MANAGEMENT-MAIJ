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
                <h3 class="text-h3 text-text">Pengesahan Dua Faktor</h3>
                <p class="text-small text-secondary mt-1">Lindungi akaun anda dengan lapisan keselamatan tambahan. Gunakan Google Authenticator atau Authy.</p>
            </div>
            <button @click="startEnrollment()" type="button"
                class="btn btn-primary btn-sm shrink-0">
                <i class="fa-solid fa-shield-halved text-[12px]"></i>
                Aktifkan 2FA
            </button>
        </div>
    </div>

    {{-- Enrolling: Show QR + code input --}}
    <div x-show="state === 'enrolling'" x-cloak>
        <h3 class="text-h3 text-text mb-1">Sediakan 2FA</h3>
        <p class="text-small text-secondary mb-4">Imbas kod QR dengan aplikasi pengesah anda, kemudian masukkan kod 6 digit.</p>

        <div class="flex flex-col sm:flex-row items-center gap-5 mb-4">
            <canvas x-ref="qrCanvas" class="shrink-0 bg-white p-3 rounded-[16px] shadow-sm" width="200" height="200"></canvas>
            <div class="flex-1 min-w-0 space-y-2">
                <p class="text-eyebrow text-tertiary">Kod Manual</p>
                <code class="block text-[13px] font-mono break-all bg-muted p-2 rounded-[8px] text-text" x-text="secret"></code>
                <p class="text-small text-secondary">Salin kod ini jika tidak dapat mengimbas QR.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 max-w-xs">
            <input type="text" x-model="code" maxlength="6" placeholder="000000" @input="error = ''"
                class="input-base text-center text-[18px] tracking-[8px] font-mono w-full sm:w-40" autocomplete="one-time-code" inputmode="numeric">
            <button @click="verifyEnrollment()" :disabled="loading || code.length !== 6"
                class="btn btn-primary btn-sm" x-text="loading ? 'Mengesahkan...' : 'Sahkan'"></button>
        </div>
        <p x-show="error" x-text="error" class="text-small text-danger mt-2"></p>
        <button @click="state = 'idle'" class="btn btn-ghost btn-sm mt-3">Batal</button>
    </div>

    {{-- Enabled: Show status + disable button --}}
    <div x-show="isEnabled && state !== 'enrolling'" x-cloak>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-h3 text-text flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-success text-[14px]"></i>
                    Pengesahan Dua Faktor
                </h3>
                <p class="text-small text-success font-semibold mt-1">Aktif — Akaun anda dilindungi dengan 2FA.</p>
            </div>
            <button @click="disable2FA()" type="button"
                class="btn btn-destructive btn-sm shrink-0">
                Nyahaktifkan
            </button>
        </div>

        {{-- Password confirmation prompt --}}
        <div x-show="showPasswordPrompt" x-cloak class="mt-4 p-4 soft-panel">
            <p class="text-small text-text font-medium mb-3">Sila masukkan kata laluan untuk nyahaktifkan 2FA:</p>
            <div class="flex items-center gap-2">
                <input type="password" x-model="disablePassword" placeholder="Kata laluan semasa"
                    class="input-base w-full sm:w-56" @keydown.enter="confirmDisable2FA()">
                <button @click="confirmDisable2FA()" :disabled="loading || !disablePassword"
                    class="btn btn-destructive btn-sm" x-text="loading ? 'Membuang...' : 'Sahkan'"></button>
                <button @click="cancelDisable2FA()" :disabled="loading"
                    class="btn btn-ghost btn-sm">Batal</button>
            </div>
            <p x-show="error" x-text="error" class="text-small text-danger mt-2"></p>
        </div>
    </div>

    {{-- Loading spinner --}}
    <div x-show="loading && state === 'idle'" x-cloak class="flex items-center gap-2 text-secondary">
        <i class="fa-solid fa-spinner fa-spin"></i> Memproses...
    </div>
</div>
