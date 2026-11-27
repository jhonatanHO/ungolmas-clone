<div data-news-image class="bg-news-panel {{ $aspectClass }} relative h-full w-full overflow-hidden">
    @if ($desktop)
        <picture>
            @if ($mobile)
                <source media="(max-width: 499px)" srcset="{{ $mobile }}">
            @endif
            <img src="{{ $desktop }}" alt="{{ $alt }}" loading="{{ $eager ? 'eager' : 'lazy' }}"
                @if ($eager) fetchpriority="high" @endif decoding="async"
                referrerpolicy="no-referrer" width="1116" height="628"
                class="absolute inset-0 block h-full w-full object-cover">
        </picture>
    @endif
    <div data-news-image-fallback @class([
        'absolute inset-0 items-center justify-center',
        'hidden' => $desktop !== null,
    ])>
        <svg viewBox="0 0 400 225" class="text-news-muted h-full w-full" role="img"
            aria-label="Imagen no disponible">
            <rect width="400" height="225" fill="currentColor" opacity=".12" />
            <circle cx="200" cy="100" r="32" fill="none" stroke="currentColor" stroke-width="3" />
            <path d="m200 78 20 14-8 23h-24l-8-23 20-14Zm-12 37-12 17m36-17 12 17m-44-40-11-4m51 4 11-4m-31-10V68"
                fill="none" stroke="currentColor" stroke-width="3" />
            <text x="200" y="170" text-anchor="middle" fill="currentColor" font-size="17"
                font-family="sans-serif">UNGOLMAS · NOTICIAS</text>
        </svg>
    </div>
</div>
