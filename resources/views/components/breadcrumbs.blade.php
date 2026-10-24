@php
    $crumbs = [
        'dashboard'         => [['label' => 'Utama', 'href' => null], ['label' => 'Dashboard', 'href' => null]],
        'profilasnaf'       => [['label' => 'Utama', 'href' => null], ['label' => 'Profil Asnaf', 'href' => null]],
        'viewprofile.index' => [['label' => 'Utama', 'href' => null], ['label' => 'Senarai Asnaf', 'href' => null]],
        'viewprofile'       => [['label' => 'Utama', 'href' => null], ['label' => 'Senarai Asnaf', 'href' => route('viewprofile.index')], ['label' => 'Profil', 'href' => null]],
        'history'           => [['label' => 'Utama', 'href' => null], ['label' => 'Senarai Permohonan', 'href' => null]],
        'ai.page'           => [['label' => 'Penilaian AI', 'href' => null], ['label' => 'AI Assistant', 'href' => null]],
        'hadkifayah'        => [['label' => 'Penilaian AI', 'href' => null], ['label' => 'Kalkulator Had Al-Kifayah', 'href' => null]],
        'hadkifayah.settings' => [['label' => 'Sistem', 'href' => null], ['label' => 'Tetapan Had Kifayah', 'href' => null]],
        'ai-assessment'     => [['label' => 'Penilaian AI', 'href' => null], ['label' => 'AI Assessment', 'href' => null]],
        'importcsv'         => [['label' => 'Pengurusan Data', 'href' => null], ['label' => 'Import CSV', 'href' => null]],
        'import-pdf.index'  => [['label' => 'Pengurusan Data', 'href' => null], ['label' => 'Import PDF', 'href' => null]],
        'import-pdf.preview'=> [['label' => 'Pengurusan Data', 'href' => null], ['label' => 'Import PDF', 'href' => route('import-pdf.index')], ['label' => 'Pratinjau', 'href' => null]],
        'import-pdf.history'=> [['label' => 'Pengurusan Data', 'href' => null], ['label' => 'Import PDF', 'href' => route('import-pdf.index')], ['label' => 'Sejarah', 'href' => null]],
        'reports'           => [['label' => 'Pengurusan Data', 'href' => null], ['label' => 'Laporan', 'href' => null]],
        'profile'           => [['label' => 'Sistem', 'href' => null], ['label' => 'Profil Saya', 'href' => null]],
        'settings'          => [['label' => 'Sistem', 'href' => null], ['label' => 'Tetapan', 'href' => null]],
        'user.dashboard'         => [['label' => 'Utama', 'href' => null], ['label' => 'Dashboard', 'href' => null]],
        'user.applications.index'=> [['label' => 'Utama', 'href' => null], ['label' => 'Permohonan Saya', 'href' => null]],
        'user.applications.create'=>[['label' => 'Utama', 'href' => null], ['label' => 'Permohonan Saya', 'href' => route('user.applications.index')], ['label' => 'Mohon Baharu', 'href' => null]],
        'user.applications.show' => [['label' => 'Utama', 'href' => null], ['label' => 'Permohonan Saya', 'href' => route('user.applications.index')], ['label' => 'Butiran', 'href' => null]],
        'user.applications.store'=>[['label' => 'Utama', 'href' => null], ['label' => 'Permohonan Saya', 'href' => route('user.applications.index')], ['label' => 'Cipta', 'href' => null]],
        'user.hadkifayah'        => [['label' => 'Utama', 'href' => null], ['label' => 'Kalkulator Had Al-Kifayah', 'href' => null]],
        'user.profile'           => [['label' => 'Utama', 'href' => null], ['label' => 'Profil Saya', 'href' => null]],
    ];
    $current = Route::currentRouteName();
    $items = $crumbs[$current] ?? [['label' => 'Utama', 'href' => null], ['label' => ucwords(str_replace('-', ' ', $current)), 'href' => null]];
@endphp
<nav aria-label="Breadcrumb" class="mb-1">
    <ol class="flex items-center flex-wrap text-small text-secondary">
        @foreach($items as $i => $item)
            @if($i > 0) <x-icon name="chevron-right" :size="13" class="mx-1 text-tertiary/60 shrink-0" /> @endif
            <li class="breadcrumb-item {{ $i === count($items) - 1 ? 'is-current' : '' }}">
                @if($item['href'] && $i < count($items) - 1)
                    <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                @else
                    <span>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
