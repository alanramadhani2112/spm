@props([
    'label' => 'Buka menu aksi',
    'placement' => 'bottom-end',
    'buttonClass' => 'btn-light-primary',
    'width' => 'w-225px',
])

<div class="d-inline-flex align-items-center justify-content-end">
    <button type="button"
            class="btn btn-sm btn-icon {{ $buttonClass }}"
            data-kt-menu-trigger="click"
            data-kt-menu-placement="{{ $placement }}"
            aria-label="{{ $label }}"
            title="{{ $label }}">
        <i class="ki-outline ki-dots-vertical fs-2"></i>
    </button>

    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold py-3 {{ $width }}"
         data-kt-menu="true">
        {{ $slot }}
    </div>
</div>
