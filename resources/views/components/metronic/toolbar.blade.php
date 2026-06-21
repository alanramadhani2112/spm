@props(['title' => '', 'subtitle' => '', 'breadcrumb' => null])

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        @if($title)<h2 class="fs-2 fw-bold text-gray-900 mb-1">{{ $title }}</h2>@endif
        @if($subtitle)<p class="fs-7 text-muted mb-0">{{ $subtitle }}</p>@endif
    </div>
    @if(trim((string) $slot) !== '')
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
