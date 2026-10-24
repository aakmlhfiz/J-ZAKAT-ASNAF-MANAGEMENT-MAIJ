@props(['score' => 0, 'emailVerified' => false, 'phoneAdded' => false, 'photoAdded' => false, 'twoFactorEnabled' => false])
<div class="space-y-4">
    <div class="flex items-center justify-between py-2 border-b border-divider">
        <div class="flex items-center gap-2">
            <i class="fa-solid {{ $emailVerified ? 'fa-circle-check text-success' : 'fa-circle-xmark text-tertiary' }} text-[14px]"></i>
            <span class="text-body text-text">Emel Disahkan</span>
        </div>
        <span class="text-small font-semibold {{ $emailVerified ? 'text-success' : 'text-tertiary' }}">25%</span>
    </div>
    <div class="flex items-center justify-between py-2 border-b border-divider">
        <div class="flex items-center gap-2">
            <i class="fa-solid {{ $phoneAdded ? 'fa-circle-check text-success' : 'fa-circle-xmark text-tertiary' }} text-[14px]"></i>
            <span class="text-body text-text">Telefon Ditambah</span>
        </div>
        <span class="text-small font-semibold {{ $phoneAdded ? 'text-success' : 'text-tertiary' }}">25%</span>
    </div>
    <div class="flex items-center justify-between py-2 border-b border-divider">
        <div class="flex items-center gap-2">
            <i class="fa-solid {{ $photoAdded ? 'fa-circle-check text-success' : 'fa-circle-xmark text-tertiary' }} text-[14px]"></i>
            <span class="text-body text-text">Gambar Profil</span>
        </div>
        <span class="text-small font-semibold {{ $photoAdded ? 'text-success' : 'text-tertiary' }}">25%</span>
    </div>
    <div class="flex items-center justify-between py-2">
        <div class="flex items-center gap-2">
            <i class="fa-solid {{ $twoFactorEnabled ? 'fa-circle-check text-success' : 'fa-circle-xmark text-tertiary' }} text-[14px]"></i>
            <span class="text-body text-text">2FA Diaktifkan</span>
        </div>
        <span class="text-small font-semibold {{ $twoFactorEnabled ? 'text-success' : 'text-tertiary' }}">25%</span>
    </div>
</div>
