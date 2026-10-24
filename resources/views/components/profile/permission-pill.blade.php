@props(['label' => '', 'active' => true])
<span class="permission-pill {{ $active ? '' : 'inactive' }}">
    <i class="fa-solid {{ $active ? 'fa-circle-check' : 'fa-circle-xmark' }} text-[10px]"></i>
    {{ $label }}
</span>
