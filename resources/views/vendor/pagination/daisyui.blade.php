@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-base-content/50">
            แสดง {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            จาก {{ number_format($paginator->total()) }} รายการ
        </p>
        <div class="join">
            @if ($paginator->onFirstPage())
                <button type="button" class="btn btn-sm join-item btn-disabled" disabled aria-disabled="true">ก่อนหน้า</button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm join-item" rel="prev">ก่อนหน้า</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <button type="button" class="btn btn-sm join-item btn-disabled" disabled>{{ $element }}</button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button type="button" class="btn btn-sm join-item btn-active" aria-current="page">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="btn btn-sm join-item">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm join-item" rel="next">ถัดไป</a>
            @else
                <button type="button" class="btn btn-sm join-item btn-disabled" disabled aria-disabled="true">ถัดไป</button>
            @endif
        </div>
    </nav>
@endif
