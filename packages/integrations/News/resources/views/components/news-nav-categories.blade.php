@props(['wpCategories' => []])

<div class="bg-news-panel">
    <nav aria-label="Categorías de noticias"
        class="mx-auto w-full max-w-[1300px] overflow-x-auto whitespace-nowrap px-5 md:w-[96%] md:px-0 min-[1300px]:w-full">
        <ul class="mx-auto flex w-max items-center gap-[20px] py-[4px] text-[14px] leading-[20px]">
            <li class="shrink-0">
                <a href="{{ route('news.index') }}" @if (request()->routeIs('news.index')) aria-current="page" @endif
                    @class([
                        'block transition hover:text-news-accent focus-visible:outline-2 focus-visible:outline-news-accent',
                        'text-news-accent' => request()->routeIs('news.index'),
                        'text-white' => !request()->routeIs('news.index'),
                    ])>Noticias de hoy</a>
            </li>
            @foreach ($wpCategories as $navCategory)
                @if (!empty($navCategory['slug']))
                    @php($active = request()->route('category_slug') === $navCategory['slug'])
                    <li class="shrink-0">
                        <a href="{{ route('news.category', $navCategory['slug']) }}"
                            @if ($active) aria-current="page" @endif
                            @class([
                                'block transition hover:text-news-accent focus-visible:outline-2 focus-visible:outline-news-accent',
                                'text-news-accent' => $active,
                                'text-white' => !$active,
                            ])>{{ $navCategory['name'] ?? $navCategory['slug'] }}</a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
</div>
