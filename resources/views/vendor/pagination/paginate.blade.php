@if ($paginator->hasPages())
    <div class="page">
        {{$paginator->firstItem()}} - {{ $paginator->lastItem()}} / {{ $paginator->total() }}件
    </div>
    <div>
        <div class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <div class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true" class="material-symbols-outlined no-link">navigate_before</span>
                </div>
            @else
                <div>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><span class="material-symbols-outlined">navigate_before</span></a>
                </div>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <div>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><span class="material-symbols-outlined">navigate_next</span></a>
                </div>
            @else
                <div class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true" class="material-symbols-outlined no-link">navigate_next</span>
                </div>
            @endif
            </div>
    </div>
@endif
