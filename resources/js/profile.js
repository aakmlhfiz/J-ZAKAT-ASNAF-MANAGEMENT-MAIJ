import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import QRCode from 'qrcode';

// UI copy is injected by the profile.strings Blade component.
const profileT = (key, fallback) => {
    const strings = window.profileStrings || {};
    return strings[key] !== undefined ? strings[key] : fallback;
};

document.addEventListener('alpine:init', () => {
    // ── Global Dark Mode ──────────────────────────────────────
    Alpine.data('darkMode', () => ({
        dark: false,
        saveUrl: '/app/profile/settings',

        init() {
            this.dark = document.documentElement.classList.contains('dark');
            this.$watch('dark', (val) => {
                document.documentElement.classList.toggle('dark', val);
                this.savePreference(val ? 'dark' : 'light');
            });
        },

        get darkIcon() {
            if (this.dark) return 'fa-moon';
            return 'fa-sun text-warning';
        },

        cycleDarkMode() {
            this.dark = !this.dark;
        },

        async savePreference(mode) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            try {
                await fetch(this.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ dark_mode: mode }),
                });
            } catch {}
        },
    }));

    // ── Avatar Uploader ───────────────────────────────────────
    Alpine.data('avatarUploader', (uploadUrl) => ({
        preview: null,
        cropperOpen: false,
        cropper: null,
        uploading: false,

        handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.$refs.cropperImage.src = e.target.result;
                this.cropperOpen = true;

                this.$nextTick(() => {
                    if (this.cropper) this.cropper.destroy();
                    this.cropper = new Cropper(this.$refs.cropperImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        background: false,
                    });
                });
            };
            reader.readAsDataURL(file);
            input.value = '';
        },

        closeCropper() {
            if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            this.cropperOpen = false;
        },

        async cropAndUpload() {
            if (!this.cropper) return;
            this.uploading = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            try {
                const canvas = this.cropper.getCroppedCanvas({ width: 400, height: 400 });
                const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', 0.92));
                const fd = new FormData();
                fd.append('photo', blob, 'avatar.jpg');
                fd.append('_token', token);
                fd.append('_method', 'PUT');

                const res = await fetch(uploadUrl, { method: 'POST', body: fd, headers: { Accept: 'application/json' } });

                if (res.ok) {
                    this.closeCropper();
                    this.preview = canvas.toDataURL('image/jpeg');
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('photo_updated', 'Gambar profil berjaya dikemaskini.'), type: 'success', title: profileT('toast_success', 'Berjaya') } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('photo_upload_failed', 'Gagal memuat naik gambar.'), type: 'error', title: profileT('toast_error', 'Ralat') } }));
                }
            } catch {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('photo_process_error', 'Ralat semasa memproses gambar.'), type: 'error', title: profileT('toast_error', 'Ralat') } }));
            } finally {
                this.uploading = false;
            }
        },
    }));

    // ── Profile Page Tabs ─────────────────────────────────────
    Alpine.data('profilePage', () => ({
        currentTab: localStorage.getItem('profile-tab') || 'ringkasan',
        loading: false,

        init() {
            this.$watch('currentTab', (val) => localStorage.setItem('profile-tab', val));
            window.addEventListener('profile-tab', (e) => this.switchTab(e.detail));
            const hash = window.location.hash.replace('#', '');
            if (['ringkasan', 'maklumat', 'keselamatan', 'aktiviti', 'tetapan'].includes(hash)) {
                this.currentTab = hash;
            }
        },

        switchTab(tab) {
            this.loading = true;
            this.currentTab = tab;
            window.location.hash = '#' + tab;
            setTimeout(() => (this.loading = false), 150);
        },
    }));

    // ── Toast Manager ─────────────────────────────────────────
    Alpine.data('toastManager', () => ({
        toasts: [],
        counter: 0,

        add(detail) {
            const id = ++this.counter;
            this.toasts.push({ id, ...detail, removing: false });
            setTimeout(() => this.dismiss(id), 4000);
        },

        dismiss(id) {
            const idx = this.toasts.findIndex((t) => t.id === id);
            if (idx === -1) return;
            this.toasts[idx].removing = true;
            setTimeout(() => (this.toasts = this.toasts.filter((t) => t.id !== id)), 300);
        },
    }));

    // ── Password Form ─────────────────────────────────────────
    Alpine.data('passwordForm', () => ({
        password: '',
        strength: 0,

        init() {
            this.$watch('password', (val) => { this.strength = this.calc(val || ''); });
        },

        calc(pwd) {
            let s = 0;
            if (pwd.length >= 8) s++;
            if (pwd.length >= 12) s++;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) s++;
            if (/\d/.test(pwd)) s++;
            if (/[^a-zA-Z\d]/.test(pwd)) s++;
            return Math.min(s, 4);
        },

        label() {
            return [
                profileT('strength_weak', 'Lemah'),
                profileT('strength_fair', 'Sederhana'),
                profileT('strength_good', 'Baik'),
                profileT('strength_strong', 'Kuat'),
            ][this.strength - 1] || '';
        },
        cls() { return ['', 'strength-weak', 'strength-fair', 'strength-good', 'strength-strong'][this.strength] || ''; },
    }));

    // ── 2FA Flow ──────────────────────────────────────────────
    Alpine.data('twoFactorFlow', () => ({
        state: 'idle',
        qrUrl: '',
        secret: '',
        code: '',
        error: '',
        loading: false,
        isEnabled: false,
        enableUrl: '',
        verifyUrl: '',
        disableUrl: '',
        disablePassword: '',
        showPasswordPrompt: false,

        init() {
            this.isEnabled = this.$el.dataset.enabled === '1';
            this.enableUrl = this.$el.dataset.enableUrl || '';
            this.verifyUrl = this.$el.dataset.verifyUrl || '';
            this.disableUrl = this.$el.dataset.disableUrl || '';
        },

        async startEnrollment() {
            this.loading = true;
            this.error = '';
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch(this.enableUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                });
                const data = await res.json();
                this.qrUrl = data.qrUrl;
                this.secret = data.secret;
                this.state = 'enrolling';
                this.$nextTick(() => {
                    QRCode.toCanvas(this.$refs.qrCanvas, data.qrUrl, {
                        width: 200, margin: 1,
                        color: { dark: '#1E293B', light: '#FFFFFF' },
                    });
                });
            } catch {
                this.error = profileT('two_factor_start_error', 'Ralat memulakan pendaftaran 2FA.');
            }
            this.loading = false;
        },

        async verifyEnrollment() {
            if (this.code.length !== 6) return;
            this.loading = true;
            this.error = '';
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch(this.verifyUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ code: this.code }),
                });
                const data = await res.json();
                if (data.ok) {
                    this.isEnabled = true;
                    this.state = 'done';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('two_factor_enabled_toast', '2FA berjaya diaktifkan!'), type: 'success', title: '2FA' } }));
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    this.error = data.message || profileT('invalid_code_short', 'Kod tidak sah.');
                }
            } catch {
                this.error = profileT('verify_error', 'Ralat mengesahkan kod.');
            }
            this.loading = false;
        },

        async disable2FA() {
            this.showPasswordPrompt = true;
        },

        async confirmDisable2FA() {
            if (!this.disablePassword) return;
            this.loading = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch(this.disableUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ current_password: this.disablePassword }),
                });
                const data = await res.json();
                if (data.ok) {
                    this.isEnabled = false;
                    this.state = 'idle';
                    this.showPasswordPrompt = false;
                    this.disablePassword = '';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('two_factor_disabled', '2FA telah dinyahaktifkan.'), type: 'warning', title: '2FA' } }));
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    this.error = data.message || profileT('password_incorrect', 'Kata laluan tidak tepat.');
                }
            } catch {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('generic_error', 'Ralat.'), type: 'error', title: profileT('toast_error', 'Ralat') } }));
            }
            this.loading = false;
        },

        cancelDisable2FA() {
            this.showPasswordPrompt = false;
            this.disablePassword = '';
            this.error = '';
        },
    }));

    // ── Notification Preferences ──────────────────────────────
    Alpine.data('notificationPrefs', () => ({
        email: false,
        push: false,
        sms: false,
        saving: false,
        saveUrl: '',

        init() {
            this.email = this.$el.dataset.email === '1';
            this.push = this.$el.dataset.push === '1';
            this.sms = this.$el.dataset.sms === '1';
            this.saveUrl = this.$el.dataset.saveUrl || '';
        },

        async save() {
            this.saving = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                await fetch(this.saveUrl, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ notification_email: this.email, notification_push: this.push, notification_sms: this.sms }),
                });
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('notifications_saved', 'Keutamaan notifikasi disimpan.'), type: 'success', title: profileT('toast_saved', 'Disimpan') } }));
            } catch {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('save_failed', 'Gagal menyimpan.'), type: 'error', title: profileT('toast_error', 'Ralat') } }));
            }
            this.saving = false;
        },
    }));

    // ── Theme Card Toggle ─────────────────────────────────────
    Alpine.data('themeCard', () => ({
        selected: 'system',

        init() {
            this.selected = document.querySelector('meta[name="user-dark-mode"]')?.content || 'system';
        },

        async setTheme(mode) {
            this.selected = mode;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                await fetch('/app/profile/settings', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ dark_mode: mode }),
                });
            } catch {}
        },
    }));

    // ── Account Data Download ─────────────────────────────────
    Alpine.data('accountDownload', () => ({
        async download() {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch('/app/profile/download', {
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                });
                const data = await res.json();
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'account-data.json';
                a.click();
                URL.revokeObjectURL(url);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('account_downloaded', 'Data akaun berjaya dimuat turun.'), type: 'success', title: profileT('toast_downloaded', 'Dimuat Turun') } }));
            } catch {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: profileT('download_failed', 'Gagal memuat turun data.'), type: 'error', title: profileT('toast_error', 'Ralat') } }));
            }
        },
    }));

    // ── Logout Others ─────────────────────────────────────────
    Alpine.data('logoutOthers', () => ({
        async submit() {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch('/app/profile/logout-others', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                });
                const data = await res.json().catch(() => ({}));
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || profileT('all_devices_logged_out', 'Semua peranti lain telah dilog keluar.'), type: 'success', title: profileT('toast_logout', 'Log Keluar') } }));
                setTimeout(() => window.location.reload(), 600);
            } catch {}
        },
    }));
});
