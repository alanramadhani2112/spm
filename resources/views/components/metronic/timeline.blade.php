@props(['items' => []])

<div class="timeline">
    @foreach($items as $i => $item)
        <div class="timeline-item">
            <div class="timeline-line w-40px"></div>
            <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                <span class="symbol-label bg-light-{{ $item['color'] ?? 'primary' }}">
                    <i class="ki-outline {{ $item['icon'] ?? 'ki-check-circle' }} fs-5 text-{{ $item['color'] ?? 'primary' }}"></i>
                </span>
            </div>
            <div class="timeline-content mb-10 {{ $i === count($items) - 1 ? '' : 'ms-n2' }}">
                <div class="fw-bold text-gray-900 fs-7">{{ $item['title'] }}</div>
                @if(isset($item['time']))<div class="text-muted fs-8 mb-2">{{ $item['time'] }}</div>@endif
                @if(isset($item['desc']))<div class="text-gray-700 fs-7">{{ $item['desc'] }}</div>@endif
                @if(isset($item['body']))<div class="mt-2">{{ $item['body'] }}</div>@endif
            </div>
        </div>
    @endforeach
</div>
