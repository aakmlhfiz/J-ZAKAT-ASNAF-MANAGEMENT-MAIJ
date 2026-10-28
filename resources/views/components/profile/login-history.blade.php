@props(['loginLogs' => []])
@if(count($loginLogs) > 0)
    <div class="overflow-x-auto">
        <table class="app-table is-compact">
            <thead>
                <tr>
                    <th>{{ __('profile.login_history.time') }}</th>
                    <th>{{ __('profile.login_history.ip') }}</th>
                    <th>{{ __('profile.login_history.browser') }}</th>
                    <th>{{ __('profile.login_history.device') }}</th>
                    <th>{{ __('profile.login_history.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loginLogs as $log)
                    <tr>
                        <td class="text-secondary whitespace-nowrap">{{ $log->created_at->locale(app()->getLocale())->diffForHumans() }}</td>
                        <td class="text-secondary tabular-nums">{{ $log->ip_address ?? '—' }}</td>
                        <td class="text-secondary">{{ $log->browser ?? '—' }}</td>
                        <td class="text-secondary">{{ $log->device ?? '—' }}</td>
                        <td>
                            <x-status-badge :status="$log->status === 'success' ? 'success' : 'danger'">
                                {{ $log->status === 'success' ? __('profile.login_history.success') : __('profile.login_history.failed') }}
                            </x-status-badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <x-empty-state icon="fa-right-to-bracket" :title="__('profile.login_history.empty_title')" :description="__('profile.login_history.empty_desc')" />
@endif
