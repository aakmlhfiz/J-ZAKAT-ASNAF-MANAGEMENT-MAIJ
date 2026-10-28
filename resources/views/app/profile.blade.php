@extends('layouts.app', [
    'title' => __('profile.title.admin'),
    'subtitle' => __('profile.subtitle.admin'),
])
@section('content')

@php
    $name = $user->name ?? __('profile.defaults.admin_name');
    $initials = strtoupper(mb_substr($name, 0, 1));
    $photo = $user->profile_photo ?? null;
    $photoUrl = $photo ? asset('storage/' . $photo) : null;
    $email = $user->email ?? 'admin@maij.gov.my';
    $hasActivities = $recentActivities && count($recentActivities) > 0;
    $hasLoginHistory = $loginHistory && count($loginHistory) > 0;
@endphp

<div x-data="profilePage()" class="space-y-6">

    {{-- Hero Banner --}}
    <x-profile.hero
        :name="$name"
        :email="$email"
        :photoUrl="$photoUrl"
        :initials="$initials"
        :role="$stats['role_label']"
        :status="$stats['status_label']"
        :department="$stats['department']"
        :accountId="$user->id"
        :verified="$user->email_verified_at ? true : false" />

    {{-- Stats Row --}}
    <div class="profile-glass-flyout grid grid-cols-2 lg:grid-cols-4 gap-4 stagger">
        <x-stat-card
            label="{{ __('profile.stats.login_count') }}"
            :value="number_format($stats['login_count'])"
            icon="fa-right-to-bracket"
            icon-bg="bg-primary-soft" icon-color="text-primary"
            accent="primary"
            :trendUp="true" trendLabel="{{ __('profile.stats.records') }}" />
        <x-stat-card
            label="{{ __('profile.stats.account_created') }}"
            :value="$stats['account_created_date']"
            icon="fa-calendar-day"
            icon-bg="bg-info-soft" icon-color="text-info"
            accent="info"
            :trendLabel="$stats['account_created_ago']" />
        <x-stat-card
            label="{{ __('profile.stats.role') }}"
            :value="$stats['role_label']"
            icon="fa-shield-halved"
            icon-bg="bg-success-soft" icon-color="text-success"
            accent="success"
            trendLabel="{{ __('profile.stats.admin_access') }}" />
        <x-stat-card
            label="{{ __('profile.stats.security_score') }}"
            :value="$securityScore . '%'"
            icon="fa-shield-halved"
            icon-bg="bg-warning-soft" icon-color="text-warning"
            accent="warning"
            :trendUp="$securityScore >= 75" :trend="$securityScore >= 75 ? __('profile.stats.secure') : __('profile.stats.moderate')" />
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: 8 cols --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Tabs --}}
            <x-app-card class="p-0" :hover="false">
                <div class="border-b border-divider px-6 pt-5">
                    <nav class="flex items-center gap-1 overflow-x-auto -mb-px" role="tablist">
                        <x-profile.tab-button tab="ringkasan" label="{{ __('profile.tabs.summary') }}" icon="fa-house" />
                        <x-profile.tab-button tab="maklumat" label="{{ __('profile.tabs.info') }}" icon="fa-id-card" />
                        <x-profile.tab-button tab="keselamatan" label="{{ __('profile.tabs.security') }}" icon="fa-shield-halved" />
                        <x-profile.tab-button tab="aktiviti" label="{{ __('profile.tabs.activity') }}" icon="fa-clock-rotate-left"
                            :count="$hasActivities ? count($recentActivities) : null" />
                        <x-profile.tab-button tab="tetapan" label="{{ __('profile.tabs.settings') }}" icon="fa-gear" />
                    </nav>
                </div>

                <div class="p-6">
                    {{-- Skeleton Loading --}}
                    <div x-show="loading" x-cloak class="space-y-4">
                        <div class="profile-skeleton h-5 w-1/3"></div>
                        <div class="profile-skeleton h-12 w-full"></div>
                        <div class="profile-skeleton h-12 w-2/3"></div>
                    </div>

                    {{-- Tab: Ringkasan --}}
                    <div x-show="!loading && currentTab === 'ringkasan'" x-cloak class="profile-tab-panel space-y-6">
                        <div>
                            <x-section-header :title="__('profile.overview.personal_info')" :description="__('profile.overview.admin_desc')" icon="fa-id-card" />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.full_name') }}</p><p class="text-body text-text font-medium mt-1">{{ $name }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.ic_no') }}</p><p class="text-body text-text font-medium mt-1 tabular-nums">{{ $user->ic_no ?: '—' }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.email') }}</p><p class="text-body text-text font-medium mt-1">{{ $email }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.phone') }}</p><p class="text-body text-text font-medium mt-1 tabular-nums">{{ $user->phone ?: '—' }}</p></div>
                                <div class="sm:col-span-2"><p class="text-eyebrow text-tertiary">{{ __('profile.fields.address') }}</p><p class="text-body text-text font-medium mt-1">{{ $user->address ?: '—' }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.department') }}</p><p class="text-body text-text font-medium mt-1">{{ $user->department ?: '—' }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.position') }}</p><p class="text-body text-text font-medium mt-1">{{ $stats['role_label'] }}</p></div>
                            </div>
                        </div>

                        <div>
                            <x-section-header :title="__('profile.overview.recent_activity')" :description="__('profile.overview.recent_activity_desc')" icon="fa-clock-rotate-left" />
                            @if($hasActivities)
                                <ul class="relative">
                                    @foreach($recentActivities as $activity)
                                        <x-profile.activity-item
                                            :icon="$activity->activity"
                                            :description="ucfirst($activity->activity ? str_replace('_', ' ', $activity->activity) : __('profile.activity.fallback'))"
                                            :detail="$activity->description"
                                            :time="$activity->created_at->locale(app()->getLocale())->diffForHumans()"
                                            :status="'primary'"
                                            :ip="$activity->ip_address" />
                                    @endforeach
                                </ul>
                            @else
                                <x-empty-state icon="fa-clock" :title="__('profile.overview.empty_title')" :description="__('profile.overview.empty_desc')" />
                            @endif
                        </div>
                    </div>

                    {{-- Tab: Maklumat Peribadi --}}
                    <div x-show="!loading && currentTab === 'maklumat'" x-cloak class="profile-tab-panel">
                        <form method="POST" action="{{ route('profile.info.update') }}">
                            @csrf @method('PUT')
                            <x-form-section :title="__('profile.info.form_title')" :description="__('profile.info.admin_desc')" icon="fa-user-pen">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-app-input label="{{ __('profile.fields.full_name') }}" name="name" :value="old('name', $name)" icon="fa-user" required />
                                    <x-app-input label="{{ __('profile.fields.ic_no') }}" name="ic_no" :value="old('ic_no', $user->ic_no)" icon="fa-id-card" />
                                    <x-app-input label="{{ __('profile.fields.email') }}" name="email" type="email" :value="old('email', $email)" icon="fa-envelope" required />
                                    <x-app-input label="{{ __('profile.fields.phone') }}" name="phone" :value="old('phone', $user->phone)" icon="fa-phone" />
                                    <div class="sm:col-span-2">
                                        <x-app-textarea label="{{ __('profile.fields.address') }}" name="address" :value="old('address', $user->address)" rows="3" placeholder="{{ __('profile.info.address_placeholder') }}" />
                                    </div>
                                    <x-app-input label="{{ __('profile.fields.department') }}" name="department" :value="old('department', $user->department)" icon="fa-building" />
                                </div>
                            </x-form-section>
                            <div class="flex items-center gap-3 mt-6">
                                <x-app-button type="submit" variant="primary" size="md" icon="fa-floppy-disk">{{ __('profile.info.update') }}</x-app-button>
                                <x-app-button type="reset" variant="secondary" size="md">{{ __('profile.info.cancel') }}</x-app-button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab: Keselamatan --}}
                    <div x-show="!loading && currentTab === 'keselamatan'" x-cloak class="profile-tab-panel space-y-5">

                        {{-- Password --}}
                        <x-form-section :title="__('profile.security.password_title')" :description="__('profile.security.password_desc_admin')" icon="fa-key" x-data="passwordForm()">
                            <form method="POST" action="{{ route('profile.password.update') }}">
                                @csrf @method('PUT')
                                <div class="flex items-center justify-between mb-4">
                                    <p class="text-small text-secondary">
                                        <i class="fa-solid fa-clock-rotate-left mr-1.5 text-tertiary"></i>
                                        {{ __('profile.security.last_changed') }} <span class="font-semibold text-text">{{ $user->updated_at->locale(app()->getLocale())->diffForHumans() }}</span>
                                    </p>
                                </div>
                                <div class="space-y-4">
                                    <x-app-input label="{{ __('profile.security.current_password') }}" name="current_password" type="password" icon="fa-lock" required />
                                    <div>
                                        <x-app-input label="{{ __('profile.security.new_password') }}" name="password" type="password" icon="fa-lock" required placeholder="{{ __('profile.security.min_chars') }}" x-model="password" />
                                        <div class="mt-2">
                                            <div class="password-strength-bar" x-bind:class="cls()"></div>
                                            <p class="text-small text-secondary mt-1" x-show="strength > 0">
                                                {{ __('profile.security.strength') }} <span class="font-semibold" x-text="label()" x-bind:class="{ 'text-danger': strength===1, 'text-warning': strength===2, 'text-primary': strength===3, 'text-success': strength===4 }"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <x-app-input label="{{ __('profile.security.confirm_password') }}" name="password_confirmation" type="password" icon="fa-lock" required />
                                </div>
                                @error('current_password')
                                    <p class="text-small text-danger mt-3"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                                <div class="mt-5">
                                    <x-app-button type="submit" variant="primary" size="md" icon="fa-key">{{ __('profile.security.change_password') }}</x-app-button>
                                </div>
                            </form>
                        </x-form-section>

                        {{-- 2FA --}}
                        <x-app-card class="p-6" :hover="false">
                            <x-profile.two-factor-card :enabled="$user->hasTwoFactorEnabled()" />
                        </x-app-card>

                        {{-- Session --}}
                        <x-app-card class="p-6" :hover="false">
                            <x-section-header :title="__('profile.security.sessions_title')" :description="__('profile.security.sessions_desc')" icon="fa-display" />

                            @if($currentSession)
                                @php
                                    $ua = $currentSession->user_agent ?? '';
                                    $browser = __('profile.security.unknown');
                                    $os = __('profile.security.unknown');
                                    $device = __('profile.security.desktop');
                                    if (preg_match('/(Chrome)\/(\d+)/i', $ua, $m)) $browser = 'Chrome ' . $m[2];
                                    elseif (preg_match('/Firefox\/(\d+)/i', $ua, $m)) $browser = 'Firefox ' . $m[1];
                                    elseif (preg_match('/Safari\/(\d+)/i', $ua, $m) && !preg_match('/Chrome/i', $ua)) $browser = 'Safari ' . $m[1];
                                    elseif (preg_match('/Edg\/(\d+)/i', $ua, $m)) $browser = 'Edge ' . $m[1];
                                    if (preg_match('/Windows/i', $ua)) $os = 'Windows';
                                    elseif (preg_match('/Mac/i', $ua)) $os = 'macOS';
                                    if (preg_match('/Mobi|Android/i', $ua) && !preg_match('/iPad/i', $ua)) $device = __('profile.security.phone');
                                @endphp
                                <x-profile.session-item
                                    :device="$device"
                                    :browser="$browser"
                                    :os="$os"
                                    :location="'Johor Bahru, MY'"
                                    :current="true"
                                    :time="__('profile.security.session_active')" />
                            @else
                                <x-empty-state icon="fa-display" :title="__('profile.security.no_session')" />
                            @endif

                            {{-- Login History --}}
                            <h3 class="text-h3 text-text mt-6 mb-3">{{ __('profile.security.login_history') }}</h3>
                            <x-profile.login-history :loginLogs="$loginHistory" />
                        </x-app-card>

                        {{-- Logout Others --}}
                        <div x-data="logoutOthers()">
                            <button @click="submit()" type="button" class="btn btn-destructive btn-sm">
                                <i class="fa-solid fa-right-from-bracket text-[12px]"></i>
                                {{ __('profile.security.logout_others') }}
                            </button>
                        </div>
                    </div>

                    {{-- Tab: Aktiviti --}}
                    <div x-show="!loading && currentTab === 'aktiviti'" x-cloak class="profile-tab-panel">
                        <x-section-header :title="__('profile.activity.log_title')" :description="__('profile.activity.log_desc')" icon="fa-list-check" class="mb-5" />
                        @if($hasActivities)
                            <ul class="relative">
                                @foreach($recentActivities as $activity)
                                    <x-profile.activity-item
                                        :icon="$activity->activity"
                                        :description="match($activity->activity) {
                                            'login' => __('profile.activity.login'),
                                            'logout' => __('profile.activity.logout'),
                                            'profile_update' => __('profile.activity.profile_update'),
                                            'password_change' => __('profile.activity.password_change'),
                                            'photo_upload' => __('profile.activity.photo_upload'),
                                            'settings_update' => __('profile.activity.settings_update'),
                                            '2fa_enable' => __('profile.activity.2fa_enable'),
                                            '2fa_disable' => __('profile.activity.2fa_disable'),
                                            default => ucfirst(str_replace('_', ' ', $activity->activity)),
                                        }"
                                        :detail="$activity->description"
                                        :time="$activity->created_at->locale(app()->getLocale())->diffForHumans()"
                                        :status="in_array($activity->activity, ['login', 'photo_upload']) ? 'success' : (in_array($activity->activity, ['password_change', '2fa_disable']) ? 'warning' : 'primary')"
                                        :ip="$activity->ip_address" />
                                @endforeach
                            </ul>
                        @else
                            <x-empty-state icon="fa-clock-rotate-left" :title="__('profile.activity.empty_title')" :description="__('profile.activity.empty_desc')" />
                        @endif
                    </div>

                    {{-- Tab: Tetapan --}}
                    <div x-show="!loading && currentTab === 'tetapan'" x-cloak class="profile-tab-panel space-y-5">

                        {{-- Notifications --}}
                        <x-app-card class="p-6" :hover="false"
                            x-data="notificationPrefs()"
                            data-email="{{ $user->notification_email ? '1' : '0' }}"
                            data-push="{{ $user->notification_push ? '1' : '0' }}"
                            data-sms="{{ $user->notification_sms ? '1' : '0' }}"
                            data-save-url="{{ route('profile.settings.update') }}">
                            <x-section-header :title="__('profile.settings.notifications_title')" :description="__('profile.settings.notifications_desc')" icon="fa-bell" />
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <span class="relative inline-flex">
                                        <input type="checkbox" x-model="email" class="peer sr-only">
                                        <span class="w-10 h-6 rounded-pill bg-divider transition-apple peer-checked:bg-primary"></span>
                                        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-apple peer-checked:translate-x-4"></span>
                                    </span>
                                    <span class="min-w-0"><span class="block text-body text-text font-medium">{{ __('profile.settings.email') }}</span><span class="block text-small text-secondary mt-0.5">{{ __('profile.settings.email_desc') }}</span></span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <span class="relative inline-flex">
                                        <input type="checkbox" x-model="push" class="peer sr-only">
                                        <span class="w-10 h-6 rounded-pill bg-divider transition-apple peer-checked:bg-primary"></span>
                                        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-apple peer-checked:translate-x-4"></span>
                                    </span>
                                    <span class="min-w-0"><span class="block text-body text-text font-medium">{{ __('profile.settings.push') }}</span><span class="block text-small text-secondary mt-0.5">{{ __('profile.settings.push_desc') }}</span></span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <span class="relative inline-flex">
                                        <input type="checkbox" x-model="sms" class="peer sr-only">
                                        <span class="w-10 h-6 rounded-pill bg-divider transition-apple peer-checked:bg-primary"></span>
                                        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-apple peer-checked:translate-x-4"></span>
                                    </span>
                                    <span class="min-w-0"><span class="block text-body text-text font-medium">{{ __('profile.settings.sms') }}</span><span class="block text-small text-secondary mt-0.5">{{ __('profile.settings.sms_desc') }}</span></span>
                                </label>
                            </div>
                            <div class="mt-5">
                                <x-app-button type="button" variant="primary" size="sm" icon="fa-floppy-disk" @click="save()" x-bind:disabled="saving">
                                    <span x-show="!saving">{{ __('profile.settings.save') }}</span><span x-show="saving">{{ __('profile.settings.saving') }}</span>
                                </x-app-button>
                            </div>
                        </x-app-card>

                        {{-- Theme --}}
                        <x-app-card class="p-6" :hover="false">
                            <x-section-header :title="__('profile.settings.theme_title')" :description="__('profile.settings.theme_desc')" icon="fa-palette" />
                            <x-profile.dark-mode-toggle :current="$user->dark_mode ?? 'system'" />
                        </x-app-card>

                        {{-- Language --}}
                        <x-app-card class="p-6" :hover="false">
    <x-section-header :title="__('common.language')" :description="__('common.language_description')" icon="fa-language" />
    <div class="grid grid-cols-2 gap-3">
        @foreach(['ms' => ['label' => __('common.language_ms'), 'region' => 'Malaysia', 'flag' => '🇲🇾'], 'en' => ['label' => __('common.language_en'), 'region' => 'United Kingdom', 'flag' => '🇬🇧']] as $code => $language)
            <form method="POST" action="{{ route('locale.update') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $code }}">
                <button type="submit" class="theme-card surface-flat p-4 rounded-[14px] flex items-center gap-3 w-full {{ ($user->language ?? 'ms') === $code ? 'selected' : '' }}">
                    <span class="text-[28px]">{{ $language['flag'] }}</span>
                    <div>
                        <p class="text-body text-text font-semibold text-left">{{ $language['label'] }}</p>
                        <p class="text-small text-secondary text-left">{{ $language['region'] }}</p>
                    </div>
                </button>
            </form>
        @endforeach
    </div>
</x-app-card>

                    </div>
                </div>
            </x-app-card>
        </div>

        {{-- RIGHT SIDEBAR: 4 cols --}}
        <div class="lg:col-span-4 space-y-5">

            {{-- Profile Completion --}}
            <x-app-card class="p-6" :hover="false" elevated>
                <div class="text-center">
                    <p class="text-eyebrow text-tertiary mb-4">{{ __('profile.completion.title') }}</p>
                    <div class="relative inline-flex items-center justify-center">
                        <x-profile.progress-ring :percent="$completionPercent" :size="140" :stroke="10" />
                        <span class="absolute text-metric-sm text-text font-bold">{{ $completionPercent }}%</span>
                    </div>
                    <p class="text-small text-secondary mt-3">{{ $completionPercent >= 80 ? __('profile.completion.almost') : __('profile.completion.incomplete') }}</p>
                    <div class="mt-4 h-1.5 rounded-pill bg-muted overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-primary to-[#4F46E5] rounded-pill transition-all duration-700" style="width: {{ $completionPercent }}%"></div>
                    </div>
                    @if($completionPercent < 100)
                        <a href="#maklumat" @click.prevent="$dispatch('profile-tab', 'maklumat')"
                            class="inline-flex items-center gap-1.5 text-small text-primary font-semibold mt-3 hover:underline">
                            {{ __('profile.completion.complete_now') }} <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>
            </x-app-card>

            {{-- Security Score --}}
            <x-app-card class="p-6" :hover="false" elevated>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-eyebrow text-warning">{{ __('profile.sidebar.security_eyebrow') }}</p>
                        <h2 class="text-h3 text-text mt-1">{{ __('profile.sidebar.security_score') }}</h2>
                    </div>
                    <div class="text-metric-sm text-text font-bold">{{ $securityScore }}%</div>
                </div>
                <x-profile.security-score
                    :score="$securityScore"
                    :emailVerified="$user->email_verified_at ? true : false"
                    :phoneAdded="!empty($user->phone)"
                    :photoAdded="!empty($user->profile_photo)"
                    :twoFactorEnabled="$user->hasTwoFactorEnabled()" />
            </x-app-card>

            {{-- Quick Actions --}}
            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header :title="__('profile.sidebar.quick_actions')" icon="fa-bolt" />
                <div class="space-y-2">
                    <a href="#maklumat" @click.prevent="$dispatch('profile-tab', 'maklumat')"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-primary-soft text-primary flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-pen-to-square text-[13px]"></i></div>
                        {{ __('profile.sidebar.edit_profile') }}
                    </a>
                    <a href="#keselamatan" @click.prevent="$dispatch('profile-tab', 'keselamatan')"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-warning-soft text-warning flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-key text-[13px]"></i></div>
                        {{ __('profile.sidebar.change_password') }}
                    </a>
                    <a href="#keselamatan" @click.prevent="$dispatch('profile-tab', 'keselamatan')"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-danger-soft text-danger flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-shield-halved text-[13px]"></i></div>
                        {{ __('profile.sidebar.security_settings') }}
                    </a>
                    <button type="button" x-data="accountDownload()" @click="download()"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-info-soft text-info flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-download text-[13px]"></i></div>
                        {{ __('profile.sidebar.download_data') }}
                    </button>
                </div>
            </x-app-card>

            {{-- Account Information --}}
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('profile.sidebar.account_info')" icon="fa-credit-card" />
                <div class="space-y-3.5 text-body">
                    <div class="flex items-center justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('profile.sidebar.user_id') }}</span><span class="text-text font-semibold tabular-nums">#{{ $user->id }}</span></div>
                    <div class="flex items-center justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('profile.sidebar.account_created') }}</span><span class="text-text font-semibold">{{ $stats['account_created_date'] }}</span></div>
                    <div class="flex items-center justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('profile.sidebar.last_login') }}</span><span class="text-text font-semibold">{{ $stats['last_login'] ? $stats['last_login']->created_at->locale(app()->getLocale())->diffForHumans() : '—' }}</span></div>
                    <div class="flex items-center justify-between py-2"><span class="text-secondary">{{ __('profile.sidebar.status') }}</span><x-status-badge :status="$user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'warning')">{{ $stats['status_label'] }}</x-status-badge></div>
                </div>
            </x-app-card>

            {{-- System Access --}}
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('profile.sidebar.system_access')" :description="__('profile.sidebar.system_access_desc')" icon="fa-lock-open" />
                <div class="flex flex-wrap gap-2">
                    @foreach($permissions as $perm)
                        <x-profile.permission-pill :label="$perm['label']" :active="$perm['active']" />
                    @endforeach
                </div>
            </x-app-card>

        </div>
    </div>
</div>

<x-profile.toast />
<x-profile.strings />
@endsection
