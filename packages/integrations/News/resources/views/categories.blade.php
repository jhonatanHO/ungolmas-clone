@extends('news::layouts.news')

@section('content')
    <section class="pb-4 pt-[10px] min-[576px]:pb-8 md:pb-12">
        <div
            class="mx-auto flex w-full max-w-[1300px] flex-col gap-4 md:w-[96%] md:flex-row min-[992px]:gap-8 min-[1300px]:w-full">
            <div class="flex min-w-0 flex-1 flex-col gap-[30px]">
                <div
                    class="after:bg-news-divider mb-[.3rem] flex items-center gap-[25px] px-5 after:h-px after:flex-1 after:content-[''] md:px-0">
                    <h1 class="font-news-display text-news-accent my-[.3rem] text-[30px] font-medium leading-[36px]">
                        {{ $category['name'] ?? 'Noticias' }}</h1>
                </div>
                <div class="grid grid-cols-1 gap-4 px-5 md:grid-cols-3 md:px-0 min-[992px]:grid-cols-4">
                    @forelse ($posts as $post)
                        <x-news::news-block :post="$post" as="h2" />
                    @empty
                        <p class="text-news-body col-span-full py-[20px]">Todavía no hay
                            noticias en esta categoría.</p>
                    @endforelse
                </div>
                {{ $pagination->onEachSide(1)->links('news::components.pagination') }}
            </div>
            <x-news::sidebar />
        </div>
    </section>
@endsection
