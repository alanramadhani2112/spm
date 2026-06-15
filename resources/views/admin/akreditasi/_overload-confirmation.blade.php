@php
    $potentialOverloadRows = ($assessorWorkloads ?? collect())
        ->filter(fn ($workload) => (($workload['total'] ?? 0) + 1) >= 5)
        ->sortByDesc('total');
@endphp

@if($potentialOverloadRows->isNotEmpty())
    <div class="rounded border border-warning border-dashed bg-light-warning p-4">
        <div class="d-flex align-items-start gap-3">
            <i class="ki-outline ki-warning-2 fs-2 text-warning mt-1"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold text-gray-900">Konfirmasi Assignment Overload</div>
                <div class="fs-8 text-muted mt-1">Jika salah satu asesor berikut dipilih, beban aktifnya akan mencapai atau melewati 5 assignment. Sistem mewajibkan konfirmasi dan alasan.</div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach($potentialOverloadRows as $workload)
                        @php
                            $projectedTotal = ($workload['total'] ?? 0) + 1;
                            $loadColor = ($workload['total'] ?? 0) >= 5 ? 'danger' : 'warning';
                        @endphp
                        <span class="badge badge-light-{{ $loadColor }}">
                            {{ $workload['name'] ?? 'Asesor' }}: {{ $workload['total'] ?? 0 }} -> {{ $projectedTotal }}
                        </span>
                    @endforeach
                </div>
                <label class="form-check form-check-custom form-check-solid mt-4">
                    <input class="form-check-input" type="checkbox" name="overload_confirmation" value="1" @checked(old('overload_confirmation'))>
                    <span class="form-check-label fs-7 text-gray-700">Saya memahami risiko distribusi beban dan tetap melanjutkan bila asesor overload dipilih.</span>
                </label>
                @error('overload_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
@endif
