@extends('portal.layouts.app', [
    'title' => __('profile.title.portal'),
    'subtitle' => __('profile.subtitle.portal'),
])

@section('content')

@php
    $name = $user->name ?? __('profile.defaults.portal_name');
    $initials = strtoupper(mb_substr($name, 0, 1));
    $photo = $user->profile_photo ?? null;
    $photoUrl = $photo ? asset('storage/' . $photo) : null;
    $email = $user->email ?? '';
    $hasActivities = $recentActivities && count($recentActivities) > 0;
    $hasLoginHistory = $loginHistory && count($loginHistory) > 0;
    $states = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Labuan', 'WP Putrajaya'];
    $strengthLabels = [
        __('profile.security.strength_weak'),
        __('profile.security.strength_fair'),
        __('profile.security.strength_good'),
        __('profile.security.strength_strong'),
    ];
@endphp

<div x-data="profilePage()" class="space-y-6">

    {{-- Hero Banner --}}
    <div class="profile-hero rounded-[24px] px-6 md:px-12 pt-10 pb-24 mb-6 text-white relative overflow-hidden">
        <x-profile.islamic-pattern />

        <div class="flex flex-col lg:flex-row items-center lg:items-end gap-6 text-center lg:text-left relative z-[2]">
            <div class="relative shrink-0">
                <div class="w-24 h-24 md:w-[120px] md:h-[120px] rounded-full bg-white/15 flex items-center justify-center overflow-hidden border-[4px] border-white/30">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-[48px] font-bold text-white/80">{{ $initials }}</span>
                    @endif
                </div>
                <span class="avatar-online-dot"></span>
            </div>

            <div class="flex-1 min-w-0">
                <h1 class="text-[28px] md:text-[32px] font-bold tracking-tight leading-tight flex items-center justify-center lg:justify-start gap-2">
                    {{ $name }}
                    @if($user->email_verified_at)
                        <i class="fa-solid fa-circle-check text-[22px] text-green-300"></i>
                    @endif
                </h1>
                <p class="text-white/70 mt-1.5 text-sm md:text-base">{{ $email }}</p>
                <div class="flex items-center justify-center lg:justify-start gap-2 mt-3 flex-wrap">
                    <x-status-badge status="primary">{{ $stats['role_label'] }}</x-status-badge>
                    <x-status-badge :status="$user->status === 'active' ? 'success' : 'warning'">{{ $stats['status_label'] }}</x-status-badge>
                    @if($user->hasTwoFactorEnabled())
                        <x-status-badge status="info" icon="fa-shield-halved">{{ __('profile.status.two_factor_active') }}</x-status-badge>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="profile-glass-flyout grid grid-cols-2 lg:grid-cols-4 gap-4 stagger -mt-16 relative z-[3]">
        <x-stat-card label="{{ __('profile.stats.login_count') }}" :value="number_format($stats['login_count'])" icon="fa-right-to-bracket" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" :trendUp="true" trendLabel="{{ __('profile.stats.records') }}" />
        <x-stat-card label="{{ __('profile.stats.account_created') }}" :value="$stats['account_created_date']" icon="fa-calendar-day" icon-bg="bg-info-soft" icon-color="text-info" accent="info" :trendLabel="$stats['account_created_ago']" />
        <x-stat-card label="{{ __('profile.stats.application_status') }}" :value="number_format($stats['application_count'])" icon="fa-file-lines" icon-bg="bg-success-soft" icon-color="text-success" accent="success" trendLabel="{{ __('profile.stats.applications') }}" />
        <x-stat-card label="{{ __('profile.stats.security_score') }}" :value="$securityScore . '%'" icon="fa-shield-halved" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" :trendUp="$securityScore >= 75" :trend="$securityScore >= 75 ? __('profile.stats.secure') : __('profile.stats.moderate')" />
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN --}}
        <div class="lg:col-span-8 space-y-6">

            <x-app-card class="p-0" :hover="false">
                <div class="border-b border-divider px-6 pt-5">
                    <nav class="flex items-center gap-1 overflow-x-auto -mb-px" role="tablist">
                        <x-profile.tab-button tab="ringkasan" label="{{ __('profile.tabs.summary') }}" icon="fa-house" />
                        <x-profile.tab-button tab="maklumat" label="{{ __('profile.tabs.info') }}" icon="fa-id-card" />
                        <x-profile.tab-button tab="keselamatan" label="{{ __('profile.tabs.security') }}" icon="fa-shield-halved" />
                        <x-profile.tab-button tab="aktiviti" label="{{ __('profile.tabs.activity') }}" icon="fa-clock-rotate-left" :count="$hasActivities ? count($recentActivities) : null" />
                        <x-profile.tab-button tab="tetapan" label="{{ __('profile.tabs.settings') }}" icon="fa-gear" />
                    </nav>
                </div>

                <div class="p-6">

                    {{-- Tab: Ringkasan --}}
                    <div x-show="currentTab === 'ringkasan'" x-cloak class="profile-tab-panel space-y-6">
                        <div>
                            <x-section-header :title="__('profile.overview.personal_info')" :description="__('profile.overview.portal_desc')" icon="fa-id-card" />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.full_name') }}</p><p class="text-body text-text font-medium mt-1">{{ $name }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.ic_no') }}</p><p class="text-body text-text font-medium mt-1 tabular-nums">{{ $user->ic_no ?: '—' }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.email') }}</p><p class="text-body text-text font-medium mt-1">{{ $email }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.phone') }}</p><p class="text-body text-text font-medium mt-1 tabular-nums">{{ $user->phone ?: '—' }}</p></div>
                                <div class="sm:col-span-2"><p class="text-eyebrow text-tertiary">{{ __('profile.fields.address') }}</p><p class="text-body text-text font-medium mt-1">{{ $user->address ?: '—' }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.district') }}</p><p class="text-body text-text font-medium mt-1">{{ $user->district ?: '—' }}</p></div>
                                <div><p class="text-eyebrow text-tertiary">{{ __('profile.fields.state') }}</p><p class="text-body text-text font-medium mt-1">{{ $user->state ?: '—' }}</p></div>
                            </div>
                        </div>

                        <div>
                            <x-section-header :title="__('profile.overview.recent_activity')" :description="__('profile.overview.recent_activity_desc')" icon="fa-clock-rotate-left" />
                            @if($hasActivities)
                                <ul class="relative">
                                    @foreach($recentActivities as $activity)
                                        <li class="flex items-start gap-3 py-3 border-b border-divider last:border-0">
                                            <div class="w-8 h-8 rounded-[8px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                                                <i class="fa-solid {{ match($activity->action) { 'portal.login' => 'fa-right-to-bracket', 'portal.logout' => 'fa-right-from-bracket', 'portal.profile_updated' => 'fa-pen-to-square', default => 'fa-circle-info' } }} text-[13px]"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-body text-text font-medium">{{ $activity->description }}</p>
                                                <p class="text-small text-tertiary mt-0.5">
                                                    {{ $activity->created_at->locale(app()->getLocale())->diffForHumans() }}
                                                    @if($activity->ip_address)
                                                        · <span class="font-mono">{{ $activity->ip_address }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <x-empty-state icon="fa-clock" :title="__('profile.overview.empty_title')" :description="__('profile.overview.empty_desc')" />
                            @endif
                        </div>
                    </div>

                    {{-- Tab: Maklumat Peribadi --}}
                    <div x-show="currentTab === 'maklumat'" x-cloak class="profile-tab-panel">
                        <form method="POST" action="{{ route('user.profile.update') }}">
                            @csrf @method('PUT')
                            <x-form-section :title="__('profile.info.form_title')" :description="__('profile.info.portal_desc')" icon="fa-user-pen">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-app-input label="{{ __('profile.fields.full_name') }}" name="name" :value="old('name', $name)" icon="fa-user" required />
                                    <x-app-input label="{{ __('profile.fields.ic_no') }}" name="ic_no_disabled" :value="$user->ic_no" icon="fa-id-card" disabled />
                                    <x-app-input label="{{ __('profile.fields.email') }}" name="email_disabled" type="email" :value="$email" icon="fa-envelope" disabled />
                                    <x-app-input label="{{ __('profile.fields.phone') }}" name="phone" :value="old('phone', $user->phone)" icon="fa-phone" />
                                    <x-app-input label="{{ __('profile.fields.date_of_birth') }}" name="date_of_birth" type="date" :value="old('date_of_birth', $user->date_of_birth?->format('Y-m-d'))" icon="fa-calendar" />
                                    <div>
                                        <label class="block text-small font-medium text-text mb-1.5">{{ __('profile.fields.gender') }}</label>
                                        <select name="gender" class="input-base w-full">
                                            <option value="">{{ __('profile.info.select_gender') }}</option>
                                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>{{ __('profile.info.male') }}</option>
                                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>{{ __('profile.info.female') }}</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <x-app-textarea label="{{ __('profile.fields.address') }}" name="address" :value="old('address', $user->address)" rows="2" placeholder="{{ __('profile.info.address_placeholder') }}" />
                                    </div>
                                    <x-app-input label="{{ __('profile.fields.district') }}" name="district" :value="old('district', $user->district)" icon="fa-location-dot" />
                                    <x-app-input label="{{ __('profile.fields.mukim') }}" name="mukim" :value="old('mukim', $user->mukim)" icon="fa-location-pin" />
                                    <x-app-input label="{{ __('profile.fields.occupation') }}" name="occupation" :value="old('occupation', $user->occupation)" icon="fa-briefcase" />
                                    <x-app-input label="{{ __('profile.fields.monthly_income') }}" name="monthly_income" type="number" step="0.01" min="0" :value="old('monthly_income', $user->monthly_income)" icon="fa-money-bill-wave" />
                                    <div>
                                        <label class="block text-small font-medium text-text mb-1.5">{{ __('profile.fields.marital_status') }}</label>
                                        <select name="marital_status" class="input-base w-full">
                                            <option value="">{{ __('profile.info.select') }}</option>
                                            <option value="single" {{ old('marital_status', $user->marital_status) === 'single' ? 'selected' : '' }}>{{ __('profile.info.single') }}</option>
                                            <option value="married" {{ old('marital_status', $user->marital_status) === 'married' ? 'selected' : '' }}>{{ __('profile.info.married') }}</option>
                                            <option value="divorced" {{ old('marital_status', $user->marital_status) === 'divorced' ? 'selected' : '' }}>{{ __('profile.info.divorced') }}</option>
                                            <option value="widowed" {{ old('marital_status', $user->marital_status) === 'widowed' ? 'selected' : '' }}>{{ __('profile.info.widowed') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-small font-medium text-text mb-1.5">{{ __('profile.fields.state') }}</label>
                                        <select name="state" class="input-base w-full">
                                            <option value="">{{ __('profile.info.select_state') }}</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st }}" {{ old('state', $user->state) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-app-input label="{{ __('profile.fields.postcode') }}" name="postcode" :value="old('postcode', $user->postcode)" icon="fa-mailbox" maxlength="5" />
                                </div>
                            </x-form-section>
                            <div class="flex items-center gap-3 mt-6">
                                <x-app-button type="submit" variant="primary" size="md" icon="fa-floppy-disk">{{ __('profile.info.update') }}</x-app-button>
                                <x-app-button type="reset" variant="secondary" size="md">{{ __('profile.info.cancel') }}</x-app-button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab: Keselamatan --}}
                    <div x-show="currentTab === 'keselamatan'" x-cloak class="profile-tab-panel space-y-5">

                        <x-form-section :title="__('profile.security.password_title')" :description="__('profile.security.password_desc_portal')" icon="fa-key" x-data="passwordForm()">
                            <form method="POST" action="{{ route('user.profile.password.update') }}">
                                @csrf @method('PUT')
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

                        <x-app-card class="p-6" :hover="false">
                            <x-profile.two-factor-card :enabled="$user->hasTwoFactorEnabled()" />
                        </x-app-card>

                        <x-app-card class="p-6" :hover="false">
                            <x-section-header :title="__('profile.security.sessions_title')" :description="__('profile.security.sessions_desc')" icon="fa-display" />

                            @if($currentSession)
                                @php
                                    $ua = $currentSession->user_agent ?? '';
                                    $browser = __('profile.security.unknown'); $os = __('profile.security.unknown'); $device = __('profile.security.desktop');
                                    if (preg_match('/(Chrome|Chromium)\/(\d+)/i', $ua, $m)) $browser = 'Chrome ' . $m[2];
                                    elseif (preg_match('/Firefox\/(\d+)/i', $ua, $m)) $browser = 'Firefox ' . $m[1];
                                    elseif (preg_match('/Edg\/(\d+)/i', $ua, $m)) $browser = 'Edge ' . $m[1];
                                    elseif (preg_match('/Version\/(\d+).*Safari/i', $ua, $m) && !preg_match('/Chrome/i', $ua)) $browser = 'Safari ' . $m[1];
                                    if (preg_match('/Windows NT (\d+\.\d+)/i', $ua, $m)) $os = 'Windows ' . match ((float) $m[1]) { 10.0 => '10/11', 6.3 => '8.1', 6.2 => '8', 6.1 => '7', default => $m[1] };
                                    elseif (preg_match('/Mac OS X/i', $ua)) $os = 'macOS';
                                    elseif (preg_match('/Android (\d+)/i', $ua, $m)) $os = 'Android ' . $m[1];
                                    elseif (preg_match('/iPhone OS (\d+)/i', $ua, $m)) $os = 'iOS ' . $m[1];
                                    if (preg_match('/Mobi|Android/i', $ua) && !preg_match('/iPad/i', $ua)) $device = __('profile.security.phone');
                                    elseif (preg_match('/iPad|Tablet/i', $ua)) $device = __('profile.security.tablet');
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

                            <h3 class="text-h3 text-text mt-6 mb-3">{{ __('profile.security.login_history') }}</h3>
                            <x-profile.login-history :loginLogs="$loginHistory" />
                        </x-app-card>

                    </div>

                    {{-- Tab: Aktiviti --}}
                    <div x-show="currentTab === 'aktiviti'" x-cloak class="profile-tab-panel">
                        <x-section-header :title="__('profile.activity.log_title')" :description="__('profile.activity.log_desc')" icon="fa-list-check" class="mb-5" />
                        @if($hasActivities)
                            <ul class="relative">
                                @foreach($recentActivities as $activity)
                                    <li class="flex items-start gap-3 py-3 border-b border-divider last:border-0">
                                        <div class="w-8 h-8 rounded-[8px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                                            <i class="fa-solid {{ match($activity->action) { 'portal.login' => 'fa-right-to-bracket', 'portal.logout' => 'fa-right-from-bracket', 'portal.profile_updated' => 'fa-pen-to-square', default => 'fa-circle-info' } }} text-[13px]"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-body text-text font-medium">{{ $activity->description }}</p>
                                            <p class="text-small text-tertiary mt-0.5">
                                                {{ $activity->action }} · {{ $activity->created_at->locale(app()->getLocale())->translatedFormat('d M Y H:i') }}
                                                @if($activity->ip_address)
                                                    · <span class="font-mono">{{ $activity->ip_address }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <x-empty-state icon="fa-clock-rotate-left" :title="__('profile.activity.empty_title')" :description="__('profile.activity.empty_desc')" />
                        @endif
                    </div>

                    {{-- Tab: Tetapan --}}
                    <div x-show="currentTab === 'tetapan'" x-cloak class="profile-tab-panel space-y-5">

                        <x-app-card class="p-6" :hover="false" x-data="notificationPrefs()" data-email="{{ $user->notification_email ? '1' : '0' }}" data-push="{{ $user->notification_push ? '1' : '0' }}" data-sms="{{ $user->notification_sms ? '1' : '0' }}" data-save-url="{{ route('user.profile.update') }}">
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
                                    <span x-show="!saving">{{ __('profile.settings.save') }}</span>
                                    <span x-show="saving">{{ __('profile.settings.saving') }}</span>
                                </x-app-button>
                            </div>
                        </x-app-card>

                        <x-app-card class="p-6" :hover="false">
                            <x-section-header :title="__('profile.settings.theme_title')" :description="__('profile.settings.theme_desc')" icon="fa-palette" />
                            <x-profile.dark-mode-toggle :current="$user->dark_mode ?? 'system'" />
                        </x-app-card>

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

        {{-- RIGHT SIDEBAR --}}
        <div class="lg:col-span-4 space-y-5">

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
                        <a href="#maklumat" @click.prevent="$dispatch('profile-tab', 'maklumat')" class="inline-flex items-center gap-1.5 text-small text-primary font-semibold mt-3 hover:underline">
                            {{ __('profile.completion.complete_now') }} <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>
            </x-app-card>

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

            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header :title="__('profile.sidebar.quick_actions')" icon="fa-bolt" />
                <div class="space-y-2">
                    <a href="#maklumat" @click.prevent="$dispatch('profile-tab', 'maklumat')" class="flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-primary-soft text-primary flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-pen-to-square text-[13px]"></i></div>
                        {{ __('profile.sidebar.edit_profile') }}
                    </a>
                    <a href="#keselamatan" @click.prevent="$dispatch('profile-tab', 'keselamatan')" class="flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-warning-soft text-warning flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-key text-[13px]"></i></div>
                        {{ __('profile.sidebar.change_password') }}
                    </a>
                    <a href="{{ route('user.applications.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[10px] hover:bg-muted transition-fast text-body text-text font-medium group">
                        <div class="w-8 h-8 rounded-[8px] bg-success-soft text-success flex items-center justify-center shrink-0 group-hover:scale-105 transition-fast"><i class="fa-solid fa-file-lines text-[13px]"></i></div>
                        {{ __('profile.sidebar.my_applications') }}
                    </a>
                </div>
            </x-app-card>

            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('profile.sidebar.account_info')" icon="fa-credit-card" />
                <div class="space-y-3.5 text-body">
                    <div class="flex items-center justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('profile.sidebar.user_id') }}</span><span class="text-text font-semibold tabular-nums">#{{ $user->id }}</span></div>
                    <div class="flex items-center justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('profile.sidebar.account_created') }}</span><span class="text-text font-semibold">{{ $stats['account_created_date'] }}</span></div>
                    <div class="flex items-center justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('profile.sidebar.last_login') }}</span><span class="text-text font-semibold">{{ $stats['last_login'] ? $stats['last_login']->created_at->locale(app()->getLocale())->diffForHumans() : '—' }}</span></div>
                    <div class="flex items-center justify-between py-2"><span class="text-secondary">{{ __('profile.sidebar.status') }}</span><x-status-badge :status="$user->status === 'active' ? 'success' : 'warning'">{{ $stats['status_label'] }}</x-status-badge></div>
                </div>
            </x-app-card>

        </div>
    </div>
</div>

<x-profile.toast />
<x-profile.strings />

<script>
function profilePage() {
    return {
        currentTab: 'ringkasan',
        init() {
            this.$nextTick(() => {
                const hash = window.location.hash.replace('#', '');
                if (['ringkasan', 'maklumat', 'keselamatan', 'aktiviti', 'tetapan'].includes(hash)) {
                    this.currentTab = hash;
                }
            });
            window.addEventListener('profile-tab', (e) => {
                this.currentTab = e.detail;
                window.location.hash = '#' + e.detail;
            });
        },
        switchTab(tab) {
            this.currentTab = tab;
            window.location.hash = '#' + tab;
        }
    };
}

function passwordForm() {
    return {
        password: '',
        get strength() {
            const p = this.password;
            let s = 0;
            if (p.length >= 8) s++;
            if (/[A-Z]/.test(p)) s++;
            if (/[0-9]/.test(p)) s++;
            if (/[^A-Za-z0-9]/.test(p)) s++;
            return s;
        },
        cls() {
            return ['bg-danger', 'bg-warning', 'bg-primary', 'bg-success'][this.strength - 1] || 'bg-muted';
        },
        label() {
            return @json($strengthLabels)[this.strength - 1] || '';
        }
    };
}

function notificationPrefs() {
    return {
        email: this.$el?.dataset.email === '1',
        push: this.$el?.dataset.push === '1',
        sms: this.$el?.dataset.sms === '1',
        saving: false,
        async save() {
            this.saving = true;
            const url = this.$el?.dataset.saveUrl;
            try {
                const res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        notification_email: this.email ? 1 : 0,
                        notification_push: this.push ? 1 : 0,
                        notification_sms: this.sms ? 1 : 0,
                    }),
                });
                const data = await res.json();
                if (data.ok) {
                    window.dispatchEvent(new CustomEvent('toast', {detail:{message:@json(__('profile.messages.notifications_saved')),type:'success',title:@json(__('profile.tabs.settings'))}}));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {detail:{message:@json(__('profile.messages.save_failed')),type:'error',title:@json(__('profile.toast.error'))}}));
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', {detail:{message:@json(__('profile.messages.network_error')),type:'error',title:@json(__('profile.toast.error'))}}));
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>

@endsection
