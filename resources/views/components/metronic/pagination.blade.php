@props(['paginator'])

@if($paginator->hasPages())
    <div class="d-flex flex-wrap justify-content-between align-items-center pt-4">
        <div class="fs-7 text-muted">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </div>
        <ul class="pagination pagination-sm">
            @if($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link"><i class="ki-outline ki-left fs-4"></i></span></li>
            @else
                <li class="page-item"><a href="{{ $paginator->previousPageUrl() }}" class="page-link"><i class="ki-outline ki-left fs-4"></i></a></li>
            @endif
            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                </li>
            @endforeach
            @if($paginator->hasMorePages())
                <li class="page-item"><a href="{{ $paginator->nextPageUrl() }}" class="page-link"><i class="ki-outline ki-right fs-4"></i></a></li>
            @else
                <li class="page-item disabled"><span class="page-link"><i class="ki-outline ki-right fs-4"></i></span></li>
            @endif
        </ul>
    </div>
@endif
