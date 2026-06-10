@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'placeholder' => '', 'help' => null, 'error' => null, 'options' => [], 'rows' => 3])

@php $errorKey = $error ?? $name; @endphp

<div class="fv-row mb-8">
    @if ($label)
        <label for="{{ $name }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
    @endif

    @if ($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" class="form-select form-select-solid @error($errorKey) is-invalid @enderror" {{ $required ? 'required' : '' }}>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" class="form-control form-control-solid @error($errorKey) is-invalid @enderror" rows="{{ $rows }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'file')
        <input id="{{ $name }}" type="file" name="{{ $name }}" class="form-control form-control-solid @error($errorKey) is-invalid @enderror" {{ $required ? 'required' : '' }}>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" class="form-control form-control-solid @error($errorKey) is-invalid @enderror" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>
    @endif

    @if ($help)
        <div class="text-muted fs-7 mt-2">{{ $help }}</div>
    @endif

    @error($errorKey)
        <div class="fv-plugins-message-container invalid-feedback d-block"><div class="fv-help-block">{{ $message }}</div></div>
    @enderror
</div>
