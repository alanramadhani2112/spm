@props([
    'items' => [],
])

@if(count($items))
    @foreach($items as $item)
        <li class="breadcrumb-item {{ isset($item['active']) && $item['active'] ? 'text-muted' : '' }}">
            @if(isset($item['route']) && $item['route'] && !(isset($item['active']) && $item['active']))
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="">{{ $item['label'] }}</a>
            @else
                {{ $item['label'] }}
            @endif
        </li>
    @endforeach
@endif