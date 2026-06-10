@props(['title' => null, 'type' => 'flush', 'flush' => true, 'class' => '', 'bodyClass' => ''])

<div {{ $attributes->merge(['class' => trim('card ' . (($flush || $type === 'flush') ? 'card-flush ' : '') . $class)]) }}>
    @if ($title || isset($header))
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                @if ($title)
                    <h3 class="card-label fw-bold text-gray-900">{{ $title }}</h3>
                @endif
            </div>
            @isset($header)
                <div class="card-toolbar">{{ $header }}</div>
            @endisset
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">{{ $slot }}</div>

    @isset($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
