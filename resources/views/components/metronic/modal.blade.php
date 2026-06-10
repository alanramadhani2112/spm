@props(['id', 'title' => null, 'size' => 'lg', 'static' => false, 'centered' => true, 'scrollable' => false])

@php
    $sizeClass = match($size) { 'sm' => 'modal-sm', 'xl' => 'modal-xl', 'fullscreen' => 'modal-fullscreen', default => 'modal-lg' };
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" @if($static) data-bs-backdrop="static" @endif>
    <div class="modal-dialog {{ $centered ? 'modal-dialog-centered' : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }} {{ $sizeClass }}">
        <div class="modal-content">
            <div class="modal-header">
                @if ($title)<h3 class="modal-title fw-bold">{{ $title }}</h3>@endif
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-10 px-lg-17">{{ $slot }}</div>
            @isset($footer)<div class="modal-footer">{{ $footer }}</div>@endisset
        </div>
    </div>
</div>
