@php
    $paginationIconPrev = '<svg class="pagination-icon" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
    $paginationIconNext = '<svg class="pagination-icon" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>';
@endphp
@if ($paginator->hasPages())
    <nav class="modern-pagination" aria-label="Pagination Navigation">
        {{-- Mobile View --}}
        <div class="pagination-mobile d-flex d-sm-none flex-column align-items-center gap-3 mb-0">
            <div class="pagination-buttons-mobile justify-content-center w-100">
                @if ($paginator->onFirstPage())
                    <button type="button" class="btn-pagination-mobile disabled" disabled>
                        {!! $paginationIconPrev !!} Anterior
                    </button>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="btn-pagination-mobile" rel="prev">
                        {!! $paginationIconPrev !!} Anterior
                    </a>
                @endif
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="btn-pagination-mobile" rel="next">
                        Próxima {!! $paginationIconNext !!}
                    </a>
                @else
                    <button type="button" class="btn-pagination-mobile disabled" disabled>
                        Próxima {!! $paginationIconNext !!}
                    </button>
                @endif
            </div>
            <div class="pagination-info-mobile text-center w-100">
                <span class="text-muted small">
                    Mostrando <strong>{{ $paginator->firstItem() }}</strong> a <strong>{{ $paginator->lastItem() }}</strong> de <strong>{{ $paginator->total() }}</strong>
                </span>
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="pagination-desktop d-none d-sm-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="pagination-info">
                <span class="text-muted">
                    Mostrando <strong>{{ $paginator->firstItem() }}</strong> a <strong>{{ $paginator->lastItem() }}</strong> de <strong>{{ $paginator->total() }}</strong> resultados
                </span>
            </div>
            
            <div class="pagination-controls">
                <ul class="pagination-list">
                    {{-- Previous Button --}}
                    <li class="pagination-item">
                        @if ($paginator->onFirstPage())
                            <span class="pagination-link pagination-link-disabled" aria-disabled="true">
                                {!! $paginationIconPrev !!}
                            </span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link pagination-link-prev" rel="prev" aria-label="Página anterior">
                                {!! $paginationIconPrev !!}
                            </a>
                        @endif
                    </li>

                    {{-- Page Numbers --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="pagination-item pagination-item-dots">
                                <span class="pagination-dots">{{ $element }}</span>
                            </li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="pagination-item">
                                        <span class="pagination-link pagination-link-active" aria-current="page">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $url }}" class="pagination-link">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Button --}}
                    <li class="pagination-item">
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link pagination-link-next" rel="next" aria-label="Próxima página">
                                {!! $paginationIconNext !!}
                            </a>
                        @else
                            <span class="pagination-link pagination-link-disabled" aria-disabled="true">
                                {!! $paginationIconNext !!}
                            </span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endif
