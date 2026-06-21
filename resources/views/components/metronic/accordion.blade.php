@props(['id' => null, 'items' => [], 'flush' => true])

@php $accordionId = $id ?? 'accordion_' . uniqid(); @endphp

<div class="accordion {{ $flush ? 'accordion-flush' : '' }}" id="{{ $accordionId }}">
    @foreach($items as $i => $item)
        <div class="accordion-item">
            <h2 class="accordion-header" id="{{ $accordionId }}_heading_{{ $i }}">
                <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fs-6 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accordionId }}_body_{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                    @if(isset($item['icon']))<i class="ki-outline {{ $item['icon'] }} fs-4 me-3"></i>@endif
                    {{ $item['title'] }}
                    @if(isset($item['badge']))<span class="badge badge-light-{{ $item['badgeColor'] ?? 'primary' }} ms-3">{{ $item['badge'] }}</span>@endif
                </button>
            </h2>
            <div id="{{ $accordionId }}_body_{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" aria-labelledby="{{ $accordionId }}_heading_{{ $i }}" data-bs-parent="#{{ $accordionId }}">
                <div class="accordion-body">{{ $item['content'] ?? '' }}</div>
            </div>
        </div>
    @endforeach
</div>
