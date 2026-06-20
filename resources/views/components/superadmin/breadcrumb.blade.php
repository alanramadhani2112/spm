@props([
    'items' => [],
    'homeIcon' => 'ki-home',
])

@if(count($items))
    @foreach($items as $item)
        <li class="breadcrumb-item fs-7 fw-semibold {{ isset($item['active']) && $item['active'] ? 'text-gray-900' : 'text-muted' }}">
            @if(isset($item['route']) && $item['route'] && !(isset($item['active']) && $item['active']))
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                   class="text-muted text-hover-primary fw-semibold">
                    @if($loop->first && $homeIcon)
                        <i class="ki-outline {{ $homeIcon }} fs-7 me-1"></i>
                    @endif
                    {{ $item['label'] }}
                </a>
            @else
                @if($loop->first && $homeIcon && !(isset($item['route']) && $item['route']))
                    <i class="ki-outline {{ $homeIcon }} fs-7 me-1"></i>
                @endif
                <span class="{{ isset($item['active']) && $item['active'] ? 'text-gray-900 fw-bold' : '' }}">{{ $item['label'] }}</span>
            @endif
        </li>
    @endforeach
@endif
