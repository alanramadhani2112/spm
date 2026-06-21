@props(['steps' => [], 'current' => 0])

<div class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid gap-5">
    @foreach($steps as $i => $step)
        @php $state = $i < $current ? 'completed' : ($i === $current ? 'current' : 'pending'); @endphp
        <div class="stepper-item flex-stack {{ $state === 'current' ? 'current' : '' }}">
            <div class="stepper-wrapper d-flex align-items-center gap-3">
                <div class="stepper-icon w-40px h-40px">
                    <span class="symbol symbol-circle symbol-40px">
                        <span class="symbol-label bg-light-{{ $state === 'completed' ? 'success' : ($state === 'current' ? 'primary' : 'secondary') }}">
                            @if($state === 'completed')
                                <i class="ki-solid ki-check fs-5 text-white"></i>
                            @else
                                <span class="fw-bold fs-7 text-{{ $state === 'current' ? 'white' : 'gray-600' }}">{{ $i + 1 }}</span>
                            @endif
                        </span>
                    </span>
                </div>
                <div class="stepper-label">
                    <h4 class="stepper-title fs-7 fw-bold text-gray-900">{{ $step['title'] }}</h4>
                    @if(isset($step['desc']))<div class="stepper-desc fs-8 text-muted">{{ $step['desc'] }}</div>@endif
                </div>
            </div>
            @if($i < count($steps) - 1)
                <div class="stepper-line h-40px w-2px bg-light-{{ $i < $current ? 'success' : 'secondary' }} d-none d-xl-block mx-4"></div>
            @endif
        </div>
    @endforeach
</div>
