@if ($paginator->hasPages())
    <nav aria-label="Pagination Navigation">
        <div class="pagination-wrapper-modern">
            <div class="pagination-info-modern">
                <span class="text-muted">
                    Mostrando <strong class="text-success">{{ $paginator->firstItem() ?? 0 }}</strong> até 
                    <strong class="text-success">{{ $paginator->lastItem() ?? 0 }}</strong> de 
                    <strong class="text-success">{{ $paginator->total() }}</strong> resultados
                </span>
            </div>
            
            <ul class="pagination-modern-list">
                {{-- Previous Button --}}
                <li class="pagination-modern-item">
                    @if ($paginator->onFirstPage())
                        <span class="pagination-modern-link pagination-modern-link-disabled" aria-disabled="true">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="pagination-modern-link pagination-modern-link-prev" rel="prev" aria-label="Página anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif
                </li>

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="pagination-modern-item pagination-modern-item-dots">
                            <span class="pagination-modern-dots">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="pagination-modern-item">
                                    <span class="pagination-modern-link pagination-modern-link-active" aria-current="page">
                                        {{ $page }}
                                    </span>
                                </li>
                            @else
                                <li class="pagination-modern-item">
                                    <a href="{{ $url }}" class="pagination-modern-link">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Button --}}
                <li class="pagination-modern-item">
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="pagination-modern-link pagination-modern-link-next" rel="next" aria-label="Próxima página">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="pagination-modern-link pagination-modern-link-disabled" aria-disabled="true">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </li>
            </ul>
        </div>
    </nav>
@endif













