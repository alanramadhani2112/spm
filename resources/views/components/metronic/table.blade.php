@props(['headers' => [], 'striped' => true, 'dashed' => true])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table align-middle ' . ($dashed ? 'table-row-dashed ' : '') . ($striped ? 'table-striped ' : '') . 'fs-6 gy-4']) }}>
        @if(count($headers))
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    @foreach($headers as $h)
                        <th class="{{ $h['class'] ?? '' }}">{{ $h['label'] ?? $h }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="text-gray-600 fw-semibold">{{ $slot }}</tbody>
    </table>
</div>
