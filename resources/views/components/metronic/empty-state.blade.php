@props([
    'icon' => 'ki-document',
    'title' => 'Belum ada data',
    'description' => null,
    'actionLabel' => null,
    'actionRoute' => null,
])

<div class="text-center py-12 text-muted">
    <div class="symbol symbol-50px mb-6">
        <span class="symbol-label bg-light">
            <i class="ki-outline {{ $icon }} fs-1 text-gray-400"></i>
        </span>
    </div>
    <div class="fw-bold text-gray-900 fs-5 mb-2">{{ $title }}</div>
    @if($description)
        <p class="fs-7 text-muted mb-4">{{ $description }}</p>
    @endif
    @if($actionLabel && $actionRoute)
        <a href="{{ $actionRoute }}" class="btn btn-sm btn-primary">
            <i class="ki-outline ki-plus fs-4"></i>{{ $actionLabel }}
        </a>
    @endif
</div>
