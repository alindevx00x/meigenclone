@if ($paginator->hasPages())
    <nav class="custom-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-meta">
            Showing <span>{{ $paginator->firstItem() }}</span>–<span>{{ $paginator->lastItem() }}</span> of <span>{{ number_format($paginator->total()) }}</span> items
        </div>

        <div class="pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn is-disabled" aria-disabled="true" aria-label="Previous page">
                    <span>←</span> <span class="btn-label">Previous</span>
                </span>
            @else
                <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                    <span>←</span> <span class="btn-label">Previous</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            <div class="pagination-numbers">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-ellipsis" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-btn is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="pagination-btn" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                    <span class="btn-label">Next</span> <span>→</span>
                </a>
            @else
                <span class="pagination-btn is-disabled" aria-disabled="true" aria-label="Next page">
                    <span class="btn-label">Next</span> <span>→</span>
                </span>
            @endif
        </div>
    </nav>
@endif
