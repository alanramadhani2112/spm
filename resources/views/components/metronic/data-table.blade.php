@props(['id' => 'kt_datatable', 'headers' => [], 'title' => null, 'class' => '', 'paginated' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($toolbar))
        <div class="card-header align-items-center border-0">
            @if ($title)
                <h3 class="card-title fw-bold text-gray-900 m-0">{{ $title }}</h3>
            @endif
            @isset($toolbar)
                <div class="card-toolbar">{{ $toolbar }}</div>
            @endisset
        </div>
    @endif
    <div class="card-body py-4">
        <div class="table-responsive">
            <table id="{{ $id }}" class="table align-middle table-row-dashed table-striped fs-6 gy-5 {{ $class }}">
                @if (count($headers) > 0)
                    <thead><tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">@foreach ($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
                @endif
                <tbody class="text-gray-600 fw-semibold">{{ $slot }}</tbody>
            </table>
        </div>
    </div>
    @if ($paginated)
        <div class="card-footer">{{ $paginated }}</div>
    @endif
</div>
