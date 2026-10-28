@props(['application'])

@php
    $status = $application->status ?? 'draft';

    $steps = [
        ['key' => 'draft', 'label' => __('applications.timeline.draft')],
        ['key' => 'submitted', 'label' => __('applications.timeline.submitted')],
        ['key' => 'under_review', 'label' => __('applications.timeline.under_review')],
        ['key' => 'kewangan', 'label' => __('applications.timeline.finance_review')],
        ['key' => 'pegawai', 'label' => __('applications.timeline.officer_review')],
        ['key' => 'decision', 'label' => __('applications.timeline.decision')],
    ];

    $currentIndex = match ($status) {
        'draft' => 0,
        'submitted' => 1,
        'under_review' => 2,
        'approved', 'rejected' => 5,
        default => 0,
    };
@endphp

<div class="w-full">
    <ol class="flex items-start">
        @foreach ($steps as $i => $step)
            @php
                $done = $i < $currentIndex;
                $active = $i === $currentIndex;
                $isDecision = $step['key'] === 'decision';
                $isLast = $i === count($steps) - 1;
                $decisionDone = $isDecision && in_array($status, ['approved', 'rejected']);
                $decisionApproved = $status === 'approved';
            @endphp
            <li class="flex-1 min-w-0 relative">
                <div class="flex flex-col items-center">
                    <div class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-semibold border-2 transition-colors
                        {{ $done ? 'bg-success border-success text-white' : '' }}
                        {{ $active && ! $decisionDone ? 'bg-primary border-primary text-white' : '' }}
                        {{ ! $done && ! $active ? 'bg-card border-divider text-tertiary' : '' }}
                        {{ $isDecision && $decisionDone ? ($decisionApproved ? 'bg-success border-success text-white' : 'bg-danger border-danger text-white') : '' }}">
                        @if ($done && ! $isDecision)
                            <i class="fa-solid fa-check"></i>
                        @elseif ($isDecision && $decisionDone)
                            <i class="fa-solid {{ $decisionApproved ? 'fa-check' : 'fa-xmark' }}"></i>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <p class="mt-2 text-[11px] font-medium text-center leading-tight
                        {{ $done || $active || ($isDecision && $decisionDone) ? 'text-text' : 'text-tertiary' }}">
                        @if ($isDecision && $decisionDone)
                            {{ $decisionApproved ? __('applications.timeline.approved') : __('applications.timeline.rejected') }}
                        @else
                            {{ $step['label'] }}
                        @endif
                    </p>
                </div>
                @if (! $isLast)
                    <div class="absolute top-3.5 left-1/2 right-[-50%] h-0.5 -translate-y-1/2
                        {{ $i < $currentIndex ? 'bg-success' : 'bg-divider' }}"></div>
                @endif
            </li>
        @endforeach
    </ol>
</div>
