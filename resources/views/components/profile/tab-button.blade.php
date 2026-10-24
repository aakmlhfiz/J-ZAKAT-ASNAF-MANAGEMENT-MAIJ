@props(['tab' => 'ringkasan', 'label' => 'Ringkasan', 'icon' => null, 'count' => null])
<button
    @click="$dispatch('profile-tab', '{{ $tab }}')"
    :class="currentTab === '{{ $tab }}' ? 'profile-tab active' : 'profile-tab'"
    type="button"
    class="whitespace-nowrap"
    role="tab"
    x-bind:aria-selected="currentTab === '{{ $tab }}' ? 'true' : 'false'">
    @if($icon)<i class="fa-solid {{ $icon }} mr-1.5 text-[12px]"></i>@endif
    {{ $label }}
    @if($count !== null)
        <span class="ml-1.5 px-1.5 py-0.5 rounded-pill text-[10px] font-bold bg-muted text-tertiary">{{ $count }}</span>
    @endif
</button>
