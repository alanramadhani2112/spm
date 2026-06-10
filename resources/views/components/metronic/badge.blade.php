@props(['type' => 'secondary', 'label' => null, 'pill' => false, 'dot' => false])

@if ($dot)
    <span {{ $attributes->merge(['class' => "badge badge-dot badge-$type"]) }}></span>
@else
    <span {{ $attributes->merge(['class' => "badge badge-light-$type" . ($pill ? ' badge-pill' : '')]) }}>{{ $label ?? $slot }}</span>
@endif
