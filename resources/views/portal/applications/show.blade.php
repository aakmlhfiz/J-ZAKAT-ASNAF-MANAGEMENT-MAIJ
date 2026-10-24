@extends('portal.layouts.app')
@section('title', 'Butiran Permohonan — Portal Pemohon')
@section('page-title', 'Butiran Permohonan')

@section('content')
@php
    $user = auth('portal')->user();
    $statusLabel = match($application->status) {
        'draft' => 'Draf',
        'submitted' => 'Dihantar',
        'under_review' => 'Dalam Semakan',
        'approved' => 'Diluluskan',
        'rejected' => 'Ditolak',
        'incomplete' => 'Tidak Lengkap',
        default => ucfirst($application->status),
    };
    $statusColor = match($application->status) {
        'draft' => 'warning',
        'submitted' => 'primary',
        'under_review' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'incomplete' => 'warning',
        default => 'secondary',
    };
@endphp

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('user.applications.index') }}" class="w-9 h-9 rounded-lg hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-colors">
            <i class="fa-solid fa-arrow-left text-[14px]"></i>
        </a>
        <div>
            <h1 class="text-title text-text">{{ $application->application_no }}</h1>
            <p class="text-body text-secondary mt-1">{{ $application->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            @if($application->status === 'draft')
            <form method="POST" action="{{ route('user.applications.submit', $application->id) }}" onsubmit="return confirm('Hantar permohonan ini untuk semakan?')">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-paper-plane text-[12px]"></i>
                    <span>Hantar</span>
                </button>
            </form>
            @endif
            @if(in_array($application->status, ['approved', 'rejected']))
                @php $letterProfil = \App\Models\ProfilAsnaf::where('ic', $application->ic_no)->first(); @endphp
                @if($application->status === 'approved')
                    <span class="inline-flex items-center gap-2 px-3 py-2 bg-success/10 text-success rounded-lg text-sm font-semibold">
                        <i class="fa-solid fa-check-circle"></i> Diluluskan
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-2 bg-danger/10 text-danger rounded-lg text-sm font-semibold">
                        <i class="fa-solid fa-xmark-circle"></i> Ditolak
                    </span>
                @endif
                @if($letterProfil && $letterProfil->approved_at)
                    <span class="text-xs text-tertiary">{{ $letterProfil->approved_at->format('d M Y') }}</span>
                @endif
                @if($letterProfil && $letterProfil->letter_path)
                    <a href="{{ route('user.applications.letter', $application->id) }}" class="btn btn-primary btn-sm inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-[13px]"></i> Muat Turun Surat
                    </a>
                @endif
            @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-semibold bg-{{ $statusColor }}-soft text-{{ $statusColor }}">
                {{ $statusLabel }}
            </span>
            @endif
            @if($application->submitted_at)
                <a href="{{ route('user.applications.receipt', $application->id) }}" class="btn btn-outline btn-sm inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt text-[12px]"></i> Resit
                </a>
            @endif
        </div>
    </div>

    {{-- Progress Timeline --}}
    <x-app-card class="p-6 mb-5" :hover="false">
        <x-application-timeline :application="$application" />
    </x-app-card>

    @php $documentsRequested = \App\Models\ProfilAsnaf::where('ic', $application->ic_no)->whereNotNull('documents_requested_at')->exists(); @endphp
    @if(!empty($documentsRequested) && in_array($application->status, ['submitted', 'under_review']))
        <div class="mb-5 p-4 rounded-[14px] bg-warning-soft/40 border border-warning/20 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-warning mt-0.5"></i>
            <div class="flex-1">
                <p class="text-body font-semibold text-text">Dokumen tambahan diperlukan</p>
                <p class="text-small text-secondary mt-0.5">Sila muat naik dokumen yang diminta untuk meneruskan semakan permohonan anda.</p>
                <a href="{{ route('user.documents.index') }}" class="btn btn-sm btn-primary mt-2">
                    <i class="fa-solid fa-cloud-arrow-up text-[11px]"></i> Muat Naik Dokumen
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <x-app-card class="p-5">
            <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-tertiary text-[14px]"></i>
                Maklumat Pemohon
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Nama</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->full_name }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">No. KP</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->ic_no }}</dd>
                </div>
                @if($application->date_of_birth)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Tarikh Lahir</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->date_of_birth->format('d M Y') }}</dd>
                </div>
                @endif
                @if($application->gender)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Jantina</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->gender === 'male' ? 'Lelaki' : 'Perempuan' }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Telefon</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->phone }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Emel</dt>
                    <dd class="text-body text-text font-medium text-right truncate max-w-[180px]">{{ $application->email }}</dd>
                </div>
                @if($application->address)
                <div class="pt-3 border-t border-divider">
                    <dt class="text-small text-secondary mb-1">Alamat</dt>
                    <dd class="text-body text-text">{{ $application->address }}</dd>
                </div>
                @endif
                @if($application->district || $application->state)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Daerah / Negeri</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->district }}{{ $application->district && $application->state ? ', ' : '' }}{{ $application->state }}</dd>
                </div>
                @endif
                @if($application->postcode)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">Poskod</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->postcode }}</dd>
                </div>
                @endif
            </dl>
        </x-app-card>

        <div class="space-y-5">
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-people-roof text-tertiary text-[14px]"></i>
                    Isi Rumah & Pendapatan
                </h3>
                <dl class="space-y-3">
                    @if($application->marital_status)
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">Status Kahwin</dt>
                        <dd class="text-body text-text font-medium text-right">{{ ucfirst($application->marital_status) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">Bil. Isi Rumah</dt>
                        <dd class="text-body text-text font-medium text-right">{{ $application->household_count }}</dd>
                    </div>
                    @if($application->occupation)
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">Pekerjaan</dt>
                        <dd class="text-body text-text font-medium text-right">{{ $application->occupation }}</dd>
                    </div>
                    @endif
                    @if($application->income_source)
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">Sumber Pendapatan</dt>
                        <dd class="text-body text-text font-medium text-right">{{ ucfirst($application->income_source) }}</dd>
                    </div>
                    @endif
                    <div class="pt-3 border-t border-divider space-y-3">
                        <div class="flex justify-between gap-2">
                            <dt class="text-small text-secondary">Pendapatan Bulanan</dt>
                            <dd class="text-body text-text font-semibold text-right">RM {{ number_format($application->monthly_income, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-small text-secondary">Pendapatan Pasangan</dt>
                            <dd class="text-body text-text font-semibold text-right">RM {{ number_format($application->spouse_income, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-2 pt-2 border-t border-divider">
                            <dt class="text-body text-text font-semibold">Jumlah Pendapatan</dt>
                            <dd class="text-body text-text font-bold text-right">RM {{ number_format($application->total_household_income, 2) }}</dd>
                        </div>
                    </div>
                </dl>
            </x-app-card>

            @if($application->had_kifayah_result)
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-tertiary text-[14px]"></i>
                    Had Kifayah
                </h3>
                <div class="text-center py-3">
                    <p class="text-metric-lg text-text">RM {{ number_format($application->had_kifayah_result, 2) }}</p>
                    @if($application->ai_score)
                    <p class="text-small text-secondary mt-1">Skor AI: {{ $application->ai_score }}%</p>
                    @endif
                </div>
            </x-app-card>
            @endif

            @if(in_array($application->status, ['approved', 'rejected']))
            @php $suratProfil = \App\Models\ProfilAsnaf::where('ic', $application->ic_no)->first(); @endphp
            <x-app-card class="p-5" :hover="false" :accent="$application->status === 'approved' ? 'success' : 'danger'">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-tertiary text-[14px]"></i>
                    Surat & Kelulusan
                </h3>
                <div class="flex items-center gap-2 text-{{ $application->status === 'approved' ? 'success' : 'danger' }}">
                    <i class="fa-solid {{ $application->status === 'approved' ? 'fa-check-circle' : 'fa-xmark-circle' }} text-lg"></i>
                    <span class="text-sm font-semibold">{{ $application->status === 'approved' ? 'Permohonan Diluluskan' : 'Permohonan Ditolak' }}</span>
                </div>
                @if($suratProfil && $suratProfil->approved_at)
                    <p class="text-xs text-secondary mt-2">pada {{ $suratProfil->approved_at->format('d M Y, h:i A') }}</p>
                @endif
                @if($application->admin_remarks)
                    <div class="mt-3 soft-panel p-3 text-xs text-secondary">
                        <p class="text-eyebrow text-tertiary">Catatan Pentadbir</p>
                        <p class="mt-1">{{ $application->admin_remarks }}</p>
                    </div>
                @endif
                @if($suratProfil && $suratProfil->letter_path)
                    <a href="{{ route('user.applications.letter', $application->id) }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-[10px] bg-primary text-white text-sm font-semibold hover:bg-primary/90 w-full justify-center">
                        <i class="fa-solid fa-file-pdf"></i>
                        Muat Turun {{ $application->status === 'approved' ? 'Surat Kelulusan' : 'Notis Penolakan' }}
                    </a>
                @endif
            </x-app-card>
            @endif

        </div>
    </div>
</div>
@endsection
