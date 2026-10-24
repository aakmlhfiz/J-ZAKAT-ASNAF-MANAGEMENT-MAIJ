<div
    x-data="notificationsBell()"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    x-init="init()"
    class="relative"
>
    <button @click="open = !open"
            :aria-expanded="open"
            aria-label="Notifikasi"
            class="relative w-10 h-10 rounded-[12px] hover:bg-muted transition-fast flex items-center justify-center text-secondary hover:text-text">
        <x-icon name="bell" :size="19" />
        <span
            x-show="unreadCount > 0"
            class="absolute top-2 right-2 min-w-[16px] h-4 px-1 rounded-full bg-danger text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-bg"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
        ></span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full mt-2 w-[380px] max-w-[calc(100vw-2rem)] surface-elevated z-50 overflow-hidden">
        <div class="px-4 py-3 flex items-center justify-between border-b border-divider">
            <div>
                <h3 class="text-body text-text font-semibold">Notifikasi</h3>
                <p class="text-eyebrow text-tertiary mt-0.5">
                    <span x-show="unreadCount > 0" x-text="unreadCount + ' yang baru'"></span>
                    <span x-show="unreadCount === 0">Tiada notifikasi baharu</span>
                </p>
            </div>
            <button
                x-show="unreadCount > 0"
                @click="markAllRead()"
                class="text-small text-primary font-medium hover:underline">Tandai dibaca</button>
        </div>

        <ul class="max-h-[400px] overflow-y-auto p-2" x-show="notifications.length > 0">
            <template x-for="n in notifications" :key="n.id">
                <li>
                    <a :href="n.link || '#'"
                       @click.prevent="if(n.link) { window.location = n.link; } markRead(n.id)"
                       class="flex items-start gap-3 p-3 rounded-[12px] hover:bg-muted transition-fast group"
                       :class="{ 'bg-primary-soft/30': !n.read_at }">
                        <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                             :class="iconBg(n.color)">
                            <i :class="n.icon + ' text-[13px]'" :class="iconColor(n.color)"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-small text-text font-medium truncate" x-text="n.title"></p>
                                <span x-show="!n.read_at" class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                            </div>
                            <p class="text-small text-secondary mt-0.5" x-text="n.body"></p>
                        </div>
                    </a>
                </li>
            </template>
        </ul>

        <div x-show="notifications.length === 0" class="px-4 py-12 text-center text-tertiary">
            <x-icon name="bell" :size="30" class="opacity-30 mb-2 mx-auto" />
            <p class="text-small">Tiada notifikasi buat masa ini</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationsBell', () => ({
        open: false,
        notifications: [],
        unreadCount: 0,
        userId: document.querySelector('meta[name="user-id"]')?.content || '',
        userRole: document.querySelector('meta[name="user-role"]')?.content || '',
        userGuard: document.querySelector('meta[name="user-guard"]')?.content || '',
        token: document.querySelector('meta[name="csrf-token"]')?.content || '',

        async init() {
            await this.fetchUnread();

            if (window.Echo && this.userId) {
                const channel = (this.userGuard === 'portal' ? 'portal-user.' : 'user.') + this.userId;
                window.Echo.channel(channel)
                    .listen('.NotificationCreated', (data) => this.handleNewNotification(data));
            }

            if (window.Echo && this.userRole) {
                window.Echo.channel('role.' + this.userRole)
                    .listen('.NotificationCreated', (data) => this.handleNewNotification(data));
            }
        },

        async fetchUnread() {
            try {
                const res = await fetch('/api/notifications/unread', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.token,
                    },
                });
                const json = await res.json();
                this.notifications = json.notifications || [];
                this.unreadCount = json.count || 0;
            } catch (e) {
                console.warn('Failed to fetch notifications', e);
            }
        },

        handleNewNotification(data) {
            const exists = this.notifications.some(n => n.id === data.id);
            if (exists) return;

            this.notifications.unshift(data);
            if (this.notifications.length > 50) this.notifications.pop();
            this.unreadCount++;
        },

        async markRead(id) {
            const idx = this.notifications.findIndex(n => n.id === id);
            if (idx === -1) return;

            if (!this.notifications[idx].read_at) {
                this.notifications[idx].read_at = new Date().toISOString();
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }

            try {
                await fetch('/api/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.token,
                    },
                });
            } catch (e) {
                console.warn('Failed to mark notification read', e);
            }
        },

        async markAllRead() {
            const prev = this.unreadCount;
            this.notifications.forEach(n => { n.read_at = n.read_at || new Date().toISOString(); });
            this.unreadCount = 0;

            try {
                await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.token,
                    },
                });
            } catch (e) {
                this.unreadCount = prev;
                console.warn('Failed to mark all read', e);
            }
        },

        iconBg(color) {
            const map = {
                success: 'bg-success-soft text-success',
                warning: 'bg-warning-soft text-warning',
                danger: 'bg-danger-soft text-danger',
                primary: 'bg-primary-soft text-primary',
            };
            return map[color] || 'bg-muted text-secondary';
        },

        iconColor(color) {
            const map = {
                success: 'text-success',
                warning: 'text-warning',
                danger: 'text-danger',
                primary: 'text-primary',
            };
            return map[color] || 'text-secondary';
        },
    }));
});
</script>
