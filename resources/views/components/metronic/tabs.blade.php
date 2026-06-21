@props(['items' => [], 'active' => null, 'xData' => null])

<div @if($xData) x-data="{{ $xData }}" @endif>
    <ul class="nav nav-tabs nav-line-tabs mb-5">
        @foreach($items as $item)
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 {{ ($active === $item['key']) ? 'active' : '' }}"
                   href="{{ $item['url'] ?? '#' }}"
                   @if($xData && isset($item['key'])) @click="{{ $xData }}Tab = '{{ $item['key'] }}'" :class="{ active: {{ $xData }}Tab === '{{ $item['key'] }}' }" @endif>
                    @if(isset($item['icon']))<i class="ki-outline {{ $item['icon'] }} fs-4"></i>@endif
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
    {{ $slot }}
</div>
