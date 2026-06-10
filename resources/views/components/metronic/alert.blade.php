@props(['type' => 'info', 'message' => null, 'dismissible' => false])

@php
    $icons = ['success' => 'ki-check-circle', 'danger' => 'ki-information-4', 'warning' => 'ki-information-3', 'info' => 'ki-information-5'];
    $icon = $icons[$type] ?? $icons['info'];
@endphp

<div {{ $attributes->merge(['class' => "alert alert-$type d-flex align-items-center p-5 mb-6" . ($dismissible ? ' alert-dismissible' : '')]) }}>
    <i class="ki-outline {{ $icon }} fs-2hx text-{{ $type }} me-4"></i>
    <div class="d-flex flex-column flex-grow-1">
        <span>{{ $message }}</span>{{ $slot }}
    </div>
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
