<article {{ $attributes->class(['text-news-muted flex min-w-0', $layoutClass]) }} data-variant="{{ $variant }}">
    <div @class([
        'min-w-0',
        'w-full md:w-[calc(50%-5px)] md:shrink-0' => $variant === 'hero',
        'w-[94px] shrink-0 md:w-full' => $variant === 'box',
        'w-full' => in_array($variant, ['big', 'single-hero'], true),
    ])>
        @if ($linked)
            <a href="{{ $permalink }}"
                class="focus-visible:outline-news-accent block h-full focus-visible:outline-2 focus-visible:outline-offset-4"
                aria-label="{{ $title }}">
                <x-news::news-image :post="$post" :variant="$variant" />
            </a>
        @else
            <x-news::news-image :post="$post" :variant="$variant" />
        @endif
    </div>
    <div @class([
        'flex min-w-0 flex-1 flex-col px-[10px]',
        'gap-[5px]' => $variant !== 'single-hero',
        'justify-center md:w-[calc(50%-5px)] md:flex-none' => $variant === 'hero',
        'gap-[20px]' => $variant === 'single-hero',
    ])>
        @if ($variant !== 'single-hero')
            <span class="text-[12px] leading-[18px]">{{ $post['date'] ?? '' }}</span>
        @endif
        <{{ $as }} class="font-news-copy text-news-headline {{ $headingClass }} font-medium tracking-normal">
            @if ($linked)
                <a href="{{ $permalink }}"
                    class="focus-visible:outline-news-accent rounded-sm underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
            </{{ $as }}>
            @if ($showExcerpt && $excerpt !== '')
                <p @class([
                    'text-[16px] leading-[22px]',
                    'hidden md:block' => $variant === 'box',
                ])>{{ $excerpt }}</p>
            @endif
    </div>
</article>
