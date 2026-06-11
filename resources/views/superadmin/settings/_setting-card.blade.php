@php
    $currentValue = old('value', $settings[$setting['key']] ?? null);
    $currentValueForInput = is_bool($currentValue) ? ($currentValue ? '1' : '0') : (string) $currentValue;
    $displayValue = $setting['options'][$currentValueForInput] ?? $currentValue;
    if (is_bool($displayValue)) {
        $displayValue = $displayValue ? 'Ya' : 'Tidak';
    }
    $unit = $setting['unit'] ?? null;
    $modalId = 'edit-setting-'.str_replace('_', '-', $setting['key']);
    $formId = $modalId.'-form';
@endphp

<div class="rounded border border-gray-200 bg-white p-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div class="d-flex align-items-start gap-4 flex-grow-1">
            <span class="symbol symbol-40px flex-shrink-0">
                <span class="symbol-label bg-light-{{ $setting['color'] ?? 'primary' }}">
                    <i class="ki-outline {{ $setting['icon'] ?? 'ki-setting-2' }} fs-2 text-{{ $setting['color'] ?? 'primary' }}"></i>
                </span>
            </span>
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <div class="fw-bold text-gray-900">{{ $setting['label'] }}</div>
                    <span class="badge badge-light-{{ $setting['color'] ?? 'primary' }}">
                        Saat ini: {{ $displayValue ?? '—' }}{{ $unit && is_numeric($displayValue) ? ' '.$unit : '' }}
                    </span>
                </div>
                <div class="fs-8 text-muted">{{ $setting['description'] ?? 'Atur parameter workflow Super Admin.' }}</div>
                @if(! empty($setting['help']))
                    <div class="fs-8 text-muted mt-2"><i class="ki-outline ki-information-4 fs-6 me-1"></i>{{ $setting['help'] }}</div>
                @endif
            </div>
        </div>

        <button type="button"
                class="btn btn-sm btn-light-{{ $setting['color'] ?? 'primary' }}"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
                aria-label="Edit {{ $setting['label'] }}">
            <i class="ki-outline ki-pencil fs-4"></i>Edit
        </button>
    </div>
</div>

<x-metronic.modal id="{{ $modalId }}" title="Edit {{ $setting['label'] }}" size="lg">
    <form id="{{ $formId }}"
          method="POST"
          action="{{ route('superadmin.settings.update') }}"
          class="d-grid gap-5"
          data-swal-confirm="true"
          data-swal-title="Simpan perubahan {{ $setting['label'] }}?"
          data-swal-text="Setting ini akan diperbarui dan tercatat di audit log."
          data-swal-icon="warning"
          data-swal-confirm-button="Ya, simpan"
          data-swal-confirm-class="btn btn-primary">
        @csrf
        <input type="hidden" name="key" value="{{ $setting['key'] }}">

        <div class="rounded bg-light p-4">
            <div class="fs-8 text-muted mb-1">Nilai Saat Ini</div>
            <div class="fw-bold text-gray-900">{{ $displayValue ?? '—' }}{{ $unit && is_numeric($displayValue) ? ' '.$unit : '' }}</div>
        </div>

        <div>
            <label class="form-label required" for="{{ $setting['key'] }}_value">Nilai Baru</label>
            @if(($setting['type'] ?? 'text') === 'select')
                <select id="{{ $setting['key'] }}_value" name="value" class="form-select form-select-solid" required>
                    @foreach($setting['options'] ?? [] as $value => $label)
                        <option value="{{ $value }}" @selected($currentValueForInput === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @elseif(($setting['type'] ?? 'text') === 'radio')
                <div class="d-grid gap-3">
                    @foreach($setting['options'] ?? [] as $value => $label)
                        <label class="form-check form-check-custom form-check-solid rounded border border-gray-200 p-3">
                            <input class="form-check-input" type="radio" name="value" value="{{ $value }}" @checked($currentValueForInput === (string) $value) required>
                            <span class="form-check-label fw-semibold text-gray-800">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="input-group input-group-solid">
                    <input id="{{ $setting['key'] }}_value"
                           type="{{ $setting['type'] ?? 'text' }}"
                           name="value"
                           value="{{ $currentValueForInput }}"
                           class="form-control form-control-solid"
                           min="{{ $setting['min'] ?? 0 }}"
                           required>
                    @if($unit)
                        <span class="input-group-text">{{ $unit }}</span>
                    @endif
                </div>
            @endif
            @if(! empty($setting['help']))
                <div class="fs-8 text-muted mt-2">{{ $setting['help'] }}</div>
            @endif
        </div>

        <div>
            <label class="form-label required" for="{{ $setting['key'] }}_reason">Alasan Perubahan</label>
            <input id="{{ $setting['key'] }}_reason"
                   type="text"
                   name="reason"
                   class="form-control form-control-solid"
                   placeholder="{{ $setting['reason_placeholder'] ?? 'Contoh: Menyesuaikan SLA review nasional...' }}"
                   required>
            <div class="fs-8 text-muted mt-2">Wajib diisi karena perubahan tercatat di audit log.</div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" form="{{ $formId }}" class="btn btn-primary">
            <i class="ki-outline ki-check fs-4"></i>Simpan Perubahan
        </button>
    </x-slot:footer>
</x-metronic.modal>
