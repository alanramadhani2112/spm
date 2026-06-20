@props([
    'steps' => [],
    'currentIndex' => null,
    'stats' => [],
    'period' => 'all',
])

@php
    $stepCount = count($steps);
    $totalAll = array_sum(array_map(fn($s) => (int) ($s['count'] ?? 0), $steps));
@endphp

<div class="card card-flush mb-8">
    <div class="card-header align-items-center py-5">
        <div class="card-title d-flex flex-column">
            <h3 class="fw-bold text-gray-900 m-0">Alur Akreditasi</h3>
            <span class="text-muted fs-7 mt-1">Pantau posisi pengajuan di setiap tahap pipeline akreditasi.</span>
        </div>
        <div class="card-toolbar">
            <span class="badge badge-light-primary fs-7 me-2">{{ $totalAll }} dalam pipeline</span>
            <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light-primary">
                <i class="ki-outline ki-document fs-3"></i>Buka Workflow Console
            </a>
        </div>
    </div>
    <div class="card-body pt-4 pb-6">
        <div class="akreditasi-pipeline">
            <div class="pipeline-track">
                @foreach($steps as $index => $step)
                    @php
                        $isActive = $currentIndex !== null && $index === $currentIndex;
                        $isComplete = $currentIndex !== null && $index < $currentIndex;
                        $stepColor = $step['color'] ?? 'secondary';
                        $markerBg = $isActive ? 'primary' : ($isComplete ? 'success' : 'light-'.$stepColor);
                        $badgeColor = $isActive ? 'primary' : ($isComplete ? 'success' : $stepColor);
                        $labelClass = $isActive ? 'text-primary' : 'text-gray-700';
                        $statusQuery = implode(',', $step['statusFilters'] ?? []);
                        $count = (int) ($step['count'] ?? 0);
                    @endphp

                    <div class="pipeline-step {{ $isActive ? 'active' : '' }} {{ $isComplete ? 'complete' : '' }}">
                        @if(!empty($step['statusFilters']))
                            <a href="{{ route('superadmin.akreditasi.index', ['status' => $statusQuery]) }}"
                               class="pipeline-step-link text-decoration-none">
                        @else
                            <span class="pipeline-step-link text-decoration-none" style="cursor: default;">
                        @endif
                            <div class="step-marker">
                                <span class="symbol symbol-35px">
                                    <span class="symbol-label bg-{{ $markerBg }}">
                                        @if($isComplete)
                                            <i class="ki-solid ki-check fs-4 text-white"></i>
                                        @else
                                            <i class="ki-outline {{ $step['icon'] }} fs-4 {{ $isActive ? 'text-white' : 'text-'.$stepColor }}"></i>
                                        @endif
                                    </span>
                                </span>
                            </div>
                            <div class="step-label fw-semibold {{ $labelClass }}">{{ $step['label'] }}</div>
                        @if(!empty($step['statusFilters']))
                            </a>
                        @else
                            </span>
                        @endif

                        @if($count > 0)
                            <a href="{{ route('superadmin.akreditasi.index', ['status' => $statusQuery]) }}"
                               class="text-decoration-none mt-1">
                                <span class="badge badge-light-{{ $badgeColor }}">{{ $count }}</span>
                            </a>
                        @else
                            <span class="badge badge-light-secondary mt-1 opacity-50">0</span>
                        @endif
                    </div>

                    @if($index < $stepCount - 1)
                        <div class="pipeline-connector {{ $isComplete ? 'complete' : '' }} {{ $isActive ? 'active' : '' }}">
                            <span class="connector-line"></span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .akreditasi-pipeline {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: var(--bs-gray-300) transparent;
    }

    .akreditasi-pipeline::-webkit-scrollbar { height: 4px; }
    .akreditasi-pipeline::-webkit-scrollbar-track { background: transparent; }
    .akreditasi-pipeline::-webkit-scrollbar-thumb {
        background-color: var(--bs-gray-300);
        border-radius: 4px;
    }

    .pipeline-track {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        min-width: max-content;
        padding: 0.5rem 1rem;
        gap: 0;
    }

    .pipeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        min-width: 90px;
        max-width: 120px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .pipeline-step-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: inherit;
        transition: transform 0.15s ease;
    }

    .pipeline-step-link:hover {
        transform: translateY(-2px);
    }

    .step-marker {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .pipeline-step.active .step-marker .symbol-label {
        box-shadow: 0 0 0 4px rgba(0, 158, 247, 0.25);
        animation: pipeline-pulse 2s infinite;
    }

    @keyframes pipeline-pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(0, 158, 247, 0.25); }
        50% { box-shadow: 0 0 0 8px rgba(0, 158, 247, 0.1); }
    }

    .pipeline-step.complete .step-marker .symbol-label {
        box-shadow: 0 0 0 3px rgba(80, 205, 137, 0.2);
    }

    .step-label {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        line-height: 1.2;
        word-break: break-word;
        max-width: 100%;
    }

    .pipeline-connector {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        width: 36px;
        height: 35px;
        padding: 0 2px;
        position: relative;
        z-index: 0;
        align-self: flex-start;
        padding-top: 17px;
    }

    .connector-line {
        display: block;
        width: 100%;
        height: 2px;
        background-color: var(--bs-gray-300);
        border-radius: 1px;
        transition: background-color 0.3s ease;
    }

    .pipeline-connector.complete .connector-line {
        background-color: #50cd89;
    }

    .pipeline-connector.active .connector-line {
        background: linear-gradient(to right, #50cd89, #009ef7);
    }

    @media (max-width: 1199.98px) {
        .pipeline-track { flex-wrap: wrap; justify-content: flex-start; }
        .pipeline-step { min-width: 80px; max-width: 100px; }
        .pipeline-connector { width: 24px; }
    }

    @media (max-width: 767.98px) {
        .pipeline-track {
            flex-direction: column;
            align-items: flex-start;
            min-width: 100%;
            padding: 0.25rem 0.5rem;
        }
        .pipeline-step {
            flex-direction: row;
            align-items: center;
            min-width: 100%;
            max-width: 100%;
            text-align: left;
            gap: 0.75rem;
            padding: 0.5rem 0;
        }
        .pipeline-step-link {
            flex-direction: row;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            min-width: 0;
        }
        .step-label { font-size: 0.8rem; margin-top: 0; flex: 1; min-width: 0; }
        .pipeline-connector {
            width: 2px;
            height: 20px;
            margin-left: 17px;
            padding: 0;
            align-self: stretch;
        }
        .connector-line { width: 2px; height: 100%; }
    }
</style>
@endpush
