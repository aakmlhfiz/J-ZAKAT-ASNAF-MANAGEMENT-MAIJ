@extends('portal.layouts.app')
@section('title', __('notifications.page_title'))
@section('page-title', __('notifications.title'))

@section('content')
<x-page-header
    title="{{ __('notifications.title') }}"
    subtitle="{{ trans_choice('notifications.subtitle', $unread, ['count' => $unread]) }}"
/>

@if($notifications->count() === 0)
    <x-app-card>
        <x-empty-state icon="fa-bell-slash" title="{{ __('notifications.empty.title') }}" description="{{ __('notifications.empty.description') }}" />
    </x-app-card>
@else
    <x-app-card class="p-0" :hover="false">
        <div class="divide-y divide-divider">
            @foreach($notifications as $n)
                @php
                    $toneMap = ['success' => 'success', 'warning' => 'warning', 'danger' => 'danger', 'primary' => 'primary'];
                    $tone = $toneMap[$n->color] ?? 'secondary';
                @endphp
                <div class="px-6 py-4 flex items-start gap-4 {{ $n->read_at ? '' : 'bg-primary-soft/20' }}">
                    <div class="w-9 h-9 rounded-[10px] bg-{{ $tone }}-soft text-{{ $tone }} flex items-center justify-center shrink-0">
                        <i class="{{ $n->icon ?: 'fa-solid fa-bell' }} text-[13px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-body text-text font-semibold">{{ $n->title }}</p>
                            @if(!$n->read_at)<span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>@endif
                        </div>
                        <p class="text-small text-secondary mt-0.5">{{ $n->body }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    @if($n->link)
                        <a href="{{ $n->link }}" class="btn btn-sm btn-ghost shrink-0" aria-label="{{ __('notifications.open') }}">
                            <i class="fa-solid fa-arrow-right text-[11px]"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </x-app-card>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
@endif
@endsection
