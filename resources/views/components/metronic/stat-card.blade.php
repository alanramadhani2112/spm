@props(['value' => '0', 'label' => '', 'icon' => null, 'trend' => null, 'trendColor' => 'success', 'color' => 'primary', 'progress' => null])

<div {{ $attributes->merge(['class' => 'card card-flush h-md-100']) }}>
    <div class="card-header pt-5">
        <div class="card-title d-flex flex-column">
            <div class="d-flex align-items-center">
                @if ($icon)
                    <i class="ki-outline {{ $icon }} fs-2hx text-{{ $color }} me-3"></i>
                @endif
                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $value }}</span>
                @if ($trend)
                    <span class="badge badge-light-{{ $trendColor }} fs-base">{{ $trend }}</span>
                @endif
            </div>
            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ $label }}</span>
        </div>
    </div>
    @if ($progress !== null || trim((string) $slot) !== '')
        <div class="card-body pt-2 pb-4 d-flex flex-wrap align-items-center">
            @if ($progress !== null)
                <div class="progress h-8px w-100 bg-light-{{ $color }}">
                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            @endif
            {{ $slot }}
        </div>
    @endif
</div>
