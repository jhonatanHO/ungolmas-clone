@if ($paginator->hasPages())
    <nav aria-label="Paginación de noticias"
        class="flex flex-wrap items-center justify-center gap-3 px-5 text-sm md:px-0">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="Página anterior"
                class="text-news-muted bg-news-panel/20 inline-flex size-11 cursor-not-allowed items-center justify-center gap-2 rounded-full border border-transparent opacity-40 min-[400px]:w-auto min-[400px]:px-4">
                <span aria-hidden="true" class="text-lg leading-none">←</span>
                <span class="hidden min-[400px]:inline">Anterior</span>
            </span>
        @else
            <a href="{{ $paginator->currentPage() === 2 ? $paginator->path() : $paginator->previousPageUrl() }}"
                rel="prev" aria-label="Página anterior"
                class="border-news-border bg-news-panel/50 text-news-body hover:border-news-muted hover:bg-news-border inline-flex h-11 min-w-11 items-center justify-center gap-2 rounded-full border font-medium transition-colors duration-200 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white min-[400px]:px-4">
                <span aria-hidden="true" class="text-lg leading-none">←</span>
                <span class="hidden min-[400px]:inline">Anterior</span>
            </a>
        @endif

        <span class="text-news-body px-1 text-xs tabular-nums lg:sr-only">Página <strong
                class="text-news-accent font-semibold">{{ $paginator->currentPage() }}</strong> de
            {{ $paginator->lastPage() }}</span>

        <div class="hidden items-center gap-1.5 lg:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-hidden="true" class="text-news-muted px-1">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page" aria-label="Página {{ $page }}"
                                class="bg-news-accent shadow-news-accent/20 inline-flex size-11 items-center justify-center rounded-full font-bold text-black shadow-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $page === 1 ? $paginator->path() : $url }}"
                                aria-label="Ir a la página {{ $page }}"
                                class="text-news-body hover:bg-news-panel inline-flex size-11 items-center justify-center rounded-full font-medium tabular-nums transition-colors duration-200 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página siguiente"
                class="border-news-border bg-news-panel/50 text-news-body hover:border-news-muted hover:bg-news-border inline-flex h-11 min-w-11 items-center justify-center gap-2 rounded-full border font-medium transition-colors duration-200 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white min-[400px]:px-4">
                <span class="hidden min-[400px]:inline">Siguiente</span>
                <span aria-hidden="true" class="text-lg leading-none">→</span>
            </a>
        @else
            <span aria-disabled="true" aria-label="Página siguiente"
                class="text-news-muted bg-news-panel/20 inline-flex size-11 cursor-not-allowed items-center justify-center gap-2 rounded-full border border-transparent opacity-40 min-[400px]:w-auto min-[400px]:px-4">
                <span class="hidden min-[400px]:inline">Siguiente</span>
                <span aria-hidden="true" class="text-lg leading-none">→</span>
            </span>
        @endif
    </nav>
@endif
