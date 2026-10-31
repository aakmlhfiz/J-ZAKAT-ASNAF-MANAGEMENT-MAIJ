@php
    $home = __('navigation.breadcrumbs.home');
    $crumbs = [
        'dashboard'         => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.dashboard'), 'href' => null]],
        'profilasnaf'       => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.asnaf_profile'), 'href' => null]],
        'viewprofile.index' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.asnaf_list'), 'href' => null]],
        'viewprofile.legacy' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.asnaf_list'), 'href' => null]],
        'viewprofile'       => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.asnaf_list'), 'href' => route('viewprofile.index')], ['label' => __('navigation.breadcrumbs.profile'), 'href' => null]],
        'history'           => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.applications'), 'href' => null]],
        'household.dashboard' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.household_intelligence'), 'href' => null]],
        'household.search' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.household_intelligence'), 'href' => route('household.dashboard')], ['label' => __('navigation.breadcrumbs.household_search'), 'href' => null]],
        'household.show' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.household_intelligence'), 'href' => route('household.dashboard')], ['label' => __('navigation.breadcrumbs.household_profile'), 'href' => null]],
        'ai.page'           => [['label' => __('navigation.breadcrumbs.ai_assessment'), 'href' => null], ['label' => __('navigation.breadcrumbs.ai_assistant'), 'href' => null]],
        'hadkifayah'        => [['label' => __('navigation.breadcrumbs.ai_assessment'), 'href' => null], ['label' => __('navigation.breadcrumbs.had_kifayah'), 'href' => null]],
        'hadkifayah.settings' => [['label' => __('navigation.breadcrumbs.system'), 'href' => null], ['label' => __('navigation.breadcrumbs.had_kifayah_settings'), 'href' => null]],
        'ai-assessment'     => [['label' => __('navigation.breadcrumbs.ai_assessment'), 'href' => null], ['label' => __('navigation.breadcrumbs.ai_assessment'), 'href' => null]],
        'import-pdf.index'  => [['label' => __('navigation.breadcrumbs.data_management'), 'href' => null], ['label' => __('navigation.breadcrumbs.import_pdf'), 'href' => null]],
        'ocr.index'         => [['label' => __('navigation.breadcrumbs.data_management'), 'href' => null], ['label' => __('document_ocr.admin.title'), 'href' => null]],
        'ocr.show'          => [['label' => __('navigation.breadcrumbs.data_management'), 'href' => route('ocr.index')], ['label' => __('document_ocr.admin.title'), 'href' => route('ocr.index')], ['label' => __('document_ocr.admin.review'), 'href' => null]],
        'import-pdf.preview'=> [['label' => __('navigation.breadcrumbs.data_management'), 'href' => null], ['label' => __('navigation.breadcrumbs.import_pdf'), 'href' => route('import-pdf.index')], ['label' => __('navigation.breadcrumbs.preview'), 'href' => null]],
        'import-pdf.history'=> [['label' => __('navigation.breadcrumbs.data_management'), 'href' => null], ['label' => __('navigation.breadcrumbs.import_pdf'), 'href' => route('import-pdf.index')], ['label' => __('navigation.breadcrumbs.history'), 'href' => null]],
        'reports'           => [['label' => __('navigation.breadcrumbs.data_management'), 'href' => null], ['label' => __('navigation.breadcrumbs.reports'), 'href' => null]],
        'profile'           => [['label' => __('navigation.breadcrumbs.system'), 'href' => null], ['label' => __('navigation.breadcrumbs.my_profile'), 'href' => null]],
        'settings'          => [['label' => __('navigation.breadcrumbs.system'), 'href' => null], ['label' => __('navigation.breadcrumbs.settings'), 'href' => null]],
        'user.dashboard'         => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.dashboard'), 'href' => null]],
        'user.applications.index'=> [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_applications'), 'href' => null]],
        'user.applications.create'=>[['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_applications'), 'href' => route('user.applications.index')], ['label' => __('navigation.breadcrumbs.new_application'), 'href' => null]],
        'user.applications.show' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_applications'), 'href' => route('user.applications.index')], ['label' => __('navigation.breadcrumbs.details'), 'href' => null]],
        'user.applications.store'=>[['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_applications'), 'href' => route('user.applications.index')], ['label' => __('navigation.breadcrumbs.create'), 'href' => null]],
        'user.hadkifayah'        => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.had_kifayah'), 'href' => null]],
        'user.household'         => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_household'), 'href' => null]],
        'user.household.finance' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_finances'), 'href' => null]],
        'user.household.assistance' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_assistance'), 'href' => null]],
        'user.applications.status' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.application_status'), 'href' => null]],
        'user.documents.index'   => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_documents'), 'href' => null]],
        'user.letters.index'     => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.letters_receipts'), 'href' => null]],
        'user.notifications.index' => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.notifications'), 'href' => null]],
        'user.profile'           => [['label' => $home, 'href' => null], ['label' => __('navigation.breadcrumbs.my_profile'), 'href' => null]],
    ];
    $current = Route::currentRouteName();
    $items = $crumbs[$current] ?? [['label' => $home, 'href' => null], ['label' => ucwords(str_replace('-', ' ', $current)), 'href' => null]];
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
