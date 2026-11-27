@extends('news::layouts.news')

@section('content')
    <div class="mx-auto w-full max-w-[1300px] pb-4 md:w-[96%] md:pb-12 min-[1300px]:w-full">
        <nav aria-label="Ruta de navegación" class="text-news-muted px-[4px] py-[12px] text-[14px] leading-[20px]">
            <ol class="flex flex-wrap items-center gap-[10px]">
                <li><a href="{{ route('news.index') }}" class="text-news-headline hover:underline">Noticias</a></li>
                <li aria-hidden="true">›</li>
                <li><a href="{{ route('news.category', $category['slug']) }}"
                        class="text-news-headline hover:underline">{{ $category['name'] ?? 'Categoría' }}</a></li>
                <li aria-hidden="true">›</li>
                <li aria-current="page" class="line-clamp-1 max-w-xl">{{ $post['title'] }}</li>
            </ol>
        </nav>
        <div class="pb-4 pt-[10px] min-[576px]:pb-8 md:pb-12">
            <x-news::news-block :post="$post" variant="single-hero" as="h1" :show-excerpt="true"
                :should-render-link="false" />
        </div>
        <div class="flex flex-col gap-4 pt-[10px] md:flex-row min-[992px]:gap-8">
            <div class="hidden shrink-0 md:block md:w-[70px] min-[992px]:w-[140px]">
                <x-news::share-page :url="$seo['url']" :title="$post['title']" class="md:sticky md:top-[100px]" />
            </div>
            <div class="min-w-0 flex-1">
                <div data-news-content
                    class="text-news-body [&_a]:text-news-headline [&_blockquote]:border-news-accent [&_td]:border-news-border [&_th]:border-news-border break-words px-5 text-[16px] font-normal leading-[1.8rem] md:px-0 md:text-[18px] [&_a]:underline [&_blockquote]:my-4 [&_blockquote]:border-l-4 [&_blockquote]:pl-5 [&_figcaption]:text-[14px] [&_figure]:my-4 [&_h2]:my-4 [&_h2]:text-[30px] [&_h2]:text-white [&_h3]:my-4 [&_h3]:text-[25px] [&_h3]:text-white [&_h4]:my-4 [&_h4]:text-[20px] [&_h4]:text-white [&_h5]:my-4 [&_h5]:text-white [&_h6]:my-4 [&_h6]:text-white [&_img]:h-auto [&_img]:max-w-full [&_ol]:mb-2 [&_ol]:list-inside [&_ol]:list-decimal [&_p]:mb-2 [&_table]:my-4 [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto [&_td]:border [&_td]:p-2 [&_th]:border [&_th]:p-2 [&_ul]:mb-2 [&_ul]:list-inside [&_ul]:list-disc [&_video]:max-w-full">
                    {!! $contentHtml !!}
                </div>
                @if (count($popularPosts) > 0)
                    <section aria-labelledby="populares" class="mt-12">
                        <div
                            class="after:bg-news-divider mb-[.3rem] flex items-center gap-[25px] px-5 after:h-px after:flex-1 after:content-[''] md:px-0">
                            <h2 id="populares"
                                class="font-news-display my-[.3rem] text-[30px] font-medium leading-[36px] text-white">Los
                                más vistos</h2>
                        </div>
                        <div class="grid grid-cols-1 gap-4 px-5 md:grid-cols-3 md:px-0 min-[992px]:grid-cols-4">
                            @foreach ($popularPosts as $popularPost)
                                <x-news::news-block :post="$popularPost" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
            <x-news::sidebar />
        </div>
    </div>
@endsection
