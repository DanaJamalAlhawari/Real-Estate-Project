<nav class="d-flex align-items-center justify-content-between flex-wrap gap-2" aria-label="{{ __('pagination.navigation') }}">
    <span class="small text-muted">
        {{ __('Showing :first–:last of :total results', [
            'first' => $paginator->firstItem() ?? 0,
            'last' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
        ]) }}
    </span>

    <ul class="pagination">
        <li @class(['page-item', 'disabled' => $paginator->onFirstPage()])>
            @if ($paginator->onFirstPage())
                <span class="page-link" aria-disabled="true">{{ __('pagination.previous') }}</span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('pagination.previous') }}</a>
            @endif
        </li>

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @else
                @foreach ($element as $page => $url)
                    <li @class(['page-item', 'active' => $page === $paginator->currentPage()])>
                        @if ($page === $paginator->currentPage())
                            <span class="page-link" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}" aria-label="{{ __('Page :number', ['number' => $page]) }}">{{ $page }}</a>
                        @endif
                    </li>
                @endforeach
            @endif
        @endforeach

        <li @class(['page-item', 'disabled' => ! $paginator->hasMorePages()])>
            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('pagination.next') }}</a>
            @else
                <span class="page-link" aria-disabled="true">{{ __('pagination.next') }}</span>
            @endif
        </li>
    </ul>
</nav>
