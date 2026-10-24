@props(['loginLogs' => []])
@if(count($loginLogs) > 0)
    <div class="overflow-x-auto">
        <table class="app-table is-compact">
            <thead>
                <tr>
                    <th>Masa</th>
                    <th>IP</th>
                    <th>Pelayar</th>
                    <th>Peranti</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loginLogs as $log)
                    <tr>
                        <td class="text-secondary whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                        <td class="text-secondary tabular-nums">{{ $log->ip_address ?? '—' }}</td>
                        <td class="text-secondary">{{ $log->browser ?? '—' }}</td>
                        <td class="text-secondary">{{ $log->device ?? '—' }}</td>
                        <td>
                            <x-status-badge :status="$log->status === 'success' ? 'success' : 'danger'">
                                {{ $log->status === 'success' ? 'Berjaya' : 'Gagal' }}
                            </x-status-badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <x-empty-state icon="fa-right-to-bracket" title="Tiada log masuk" description="Sejarah log masuk akan dipaparkan di sini." />
@endif
