@extends('news::layouts.news')

@section('content')
    <section aria-label="Noticias de hoy" class="py-4 min-[576px]:py-8 md:py-12">
        @if ($hero)
            <div class="mx-auto mb-2 w-full max-w-[1300px] px-5 md:w-[96%] md:px-0 min-[1300px]:w-full">
                <x-news::news-block :post="$hero" variant="hero" as="h1" :show-excerpt="true" />
            </div>
        @else
            <h1
                class="font-news-display text-news-accent mx-auto mb-2 max-w-[1300px] px-5 text-[30px] font-medium leading-[36px]">
                Noticias deportivas</h1>
        @endif
        <div
            class="mx-auto flex w-full max-w-[1300px] flex-col gap-4 md:w-[96%] md:flex-row min-[992px]:gap-8 min-[1300px]:w-full">
            <div class="flex min-w-0 flex-1 flex-col gap-[30px]">
                @if (count($featured) > 0)
                    <div
                        class="after:bg-news-divider mb-[.3rem] flex items-center gap-[25px] px-5 after:h-px after:flex-1 after:content-[''] md:px-0">
                        <h2 id="destacadas"
                            class="font-news-display text-news-accent my-[.3rem] text-[30px] font-medium leading-[36px]">
                            Noticias destacadas</h2>
                    </div>
                    <div class="flex flex-col gap-4 md:flex-row">
                        <div class="min-w-0 flex-1">
                            <x-news::news-block :post="$featured[0]" variant="big" :show-excerpt="true"
                                class="md:sticky md:top-4" />
                        </div>
                        @if (count($featured) > 1)
                            <div
                                class="flex min-w-0 flex-col gap-4 px-5 md:w-[calc(40%-12px)] md:px-0 min-[992px]:w-[calc(33%-12px)]">
                                @foreach (array_slice($featured, 1, 2) as $post)
                                    <x-news::news-block :post="$post" :show-excerpt="$loop->first" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
                <div aria-label="Últimas noticias"
                    class="grid grid-cols-1 gap-4 px-5 md:grid-cols-3 md:px-0 min-[992px]:grid-cols-4">
                    @forelse ($posts as $post)
                        <x-news::news-block :post="$post" />
                    @empty
                        <p class="text-news-body col-span-full py-[20px]">No hay
                            noticias publicadas por el momento.</p>
                    @endforelse
                </div>
                {{ $pagination->onEachSide(1)->links('news::components.pagination') }}
            </div>
            <x-news::sidebar />
        </div>
    </section>
@endsection
