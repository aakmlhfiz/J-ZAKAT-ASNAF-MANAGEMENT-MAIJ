@props(['name', 'email', 'photoUrl', 'initials', 'role' => 'Pentadbir Sistem', 'status' => 'Aktif', 'department' => null, 'accountId' => null, 'verified' => false])
<div
    x-data="avatarUploader('{{ route('profile.update') }}')"
    class="profile-hero rounded-[24px] px-6 md:px-12 pt-10 pb-24 mb-6 text-white relative overflow-hidden">

    <x-profile.islamic-pattern />

    <div class="flex flex-col lg:flex-row items-center lg:items-end gap-6 text-center lg:text-left relative z-[2]">
        <div class="avatar-editor-ring relative shrink-0">
            <div class="w-24 h-24 md:w-[120px] md:h-[120px] rounded-full bg-white/15 flex items-center justify-center overflow-hidden border-[4px] border-white/30">
                <img x-show="preview || '{{ $photoUrl ? '1' : '' }}'"
                    x-bind:src="preview || '{{ $photoUrl }}'"
                    alt="{{ $name }}" class="w-full h-full object-cover"
                    @if(!$photoUrl) style="display: none;" @endif src="{{ $photoUrl }}">
                <span x-show="!preview && !('{{ $photoUrl ? '1' : '' }}')" @if($photoUrl) style="display: none;" @endif
                    class="text-[48px] font-bold text-white/80">{{ $initials }}</span>
            </div>
            <span class="avatar-online-dot"></span>
            <button @click="$refs.photoInput.click()"
                class="absolute bottom-1 right-1 w-8 h-8 rounded-full bg-white/90 text-primary flex items-center justify-center shadow-md hover:bg-white transition-fast cursor-pointer">
                <i class="fa-solid fa-camera text-[13px]"></i>
            </button>
        </div>

        <div class="flex-1 min-w-0">
            <h1 class="text-[28px] md:text-[32px] font-bold tracking-tight leading-tight flex items-center justify-center lg:justify-start gap-2">
                {{ $name }}
                @if($verified)
                    <i class="fa-solid fa-circle-check text-[22px] text-green-300"></i>
                @endif
            </h1>
            <p class="text-white/70 text-[15px] mt-1.5">{{ $email }}</p>

            <div class="flex items-center justify-center lg:justify-start gap-2.5 mt-3 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill text-[12px] font-semibold bg-white/20 text-white">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                    {{ $status }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill text-[12px] font-semibold bg-white/15 text-white/90">
                    <i class="fa-solid fa-shield-halved text-[11px]"></i>
                    {{ $role }}
                </span>
                @if($department)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill text-[12px] font-semibold bg-white/15 text-white/90">
                        <i class="fa-solid fa-building text-[11px]"></i>
                        {{ $department }}
                    </span>
                @endif
                @if($accountId)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill text-[12px] font-semibold bg-white/10 text-white/70">
                        <i class="fa-solid fa-hashtag text-[11px]"></i>
                        #{{ $accountId }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-center lg:justify-end">
            <a href="#maklumat" @click.prevent="$dispatch('profile-tab', 'maklumat')"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[10px] bg-white text-primary text-[14px] font-semibold hover:bg-white/95 transition-fast shadow-md">
                <i class="fa-solid fa-pen-to-square text-[13px]"></i>
                Edit Profil
            </a>
            <button @click="$refs.photoInput.click()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[10px] bg-white/15 text-white text-[14px] font-semibold hover:bg-white/25 transition-fast">
                <i class="fa-solid fa-image text-[13px]"></i>
                Tukar Gambar
            </button>
            <a href="#keselamatan" @click.prevent="$dispatch('profile-tab', 'keselamatan')"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[10px] bg-white/15 text-white text-[14px] font-semibold hover:bg-white/25 transition-fast">
                <i class="fa-solid fa-key text-[13px]"></i>
                Tukar K/L
            </a>
        </div>
    </div>

    <input type="file" x-ref="photoInput" accept="image/jpeg,image/png,image/webp" class="hidden"
        @change="handleFileSelect($el)">

    <div x-show="cropperOpen" x-cloak class="cropper-modal-backdrop" @click.self="closeCropper()" @keydown.escape.window="closeCropper()">
        <div class="cropper-modal-card">
            <div class="flex items-center justify-between px-6 py-4 border-b border-divider">
                <h3 class="text-h3 text-text">Laras Gambar Profil</h3>
                <button @click="closeCropper()" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-fast">
                    <i class="fa-solid fa-xmark text-[14px]"></i>
                </button>
            </div>
            <div class="cropper-preview" x-ref="cropperArea">
                <img x-ref="cropperImage" src="" alt="" class="max-w-full">
            </div>
            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-divider">
                <button @click="closeCropper()" class="btn btn-secondary btn-sm">Batal</button>
                <button @click="cropAndUpload()" x-bind:disabled="uploading"
                    class="btn btn-primary btn-sm" x-text="uploading ? 'Menyimpan...' : 'Simpan Gambar'"></button>
            </div>
        </div>
    </div>
</div>
