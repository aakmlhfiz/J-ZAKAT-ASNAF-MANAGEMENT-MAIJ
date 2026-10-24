@extends('layouts.app', [
    'title' => 'Jana Kod Undangan',
    'subtitle' => 'Cipta kod undangan pendaftaran pentadbir baharu',
])
@section('content')

<x-page-header title="Jana Kod Undangan" subtitle="Cipta kod undangan pendaftaran pentadbir baharu" eyebrow="Sistem" icon="fa-ticket" />

<div class="max-w-2xl">
    <x-app-card class="p-6" :hover="false">
        <form method="POST" action="{{ route('admin.invitations.store') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="notes" class="block text-small font-medium text-text mb-1.5">Nota (pilihan)</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Contoh: Untuk pentadbir bahagian kewangan..." class="input-base resize-y">{{ old('notes') }}</textarea>
                    <p class="text-small text-secondary mt-1.5">Nota rujukan dalaman untuk mengenal pasti tujuan kod ini.</p>
                    @error('notes')<p class="text-small text-danger mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="expires_at" class="block text-small font-medium text-text mb-1.5">Tarikh Luput (pilihan)</label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" class="input-base w-full sm:w-56" min="{{ now()->addDay()->format('Y-m-d') }}">
                    <p class="text-small text-secondary mt-1.5">Biarkan kosong untuk kod yang tidak luput.</p>
                    @error('expires_at')<p class="text-small text-danger mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6">
                <x-app-button type="submit" variant="primary" size="md" icon="fa-wand-magic-sparkles">Jana Kod Undangan</x-app-button>
                <x-app-button variant="secondary" size="md" href="{{ route('admin.invitations.index') }}">Batal</x-app-button>
            </div>
        </form>
    </x-app-card>
</div>

@endsection
