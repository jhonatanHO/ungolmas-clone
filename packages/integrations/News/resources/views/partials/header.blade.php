<header class="bg-news-border fixed inset-x-0 top-0 z-40 h-[49px] md:h-[46px]">
    <div class="relative flex h-full w-full items-center justify-between px-4">
        <span role="img" aria-label="UNGOLMAS"
            class="block h-[30px] w-[170px] shrink-0 [&_svg]:block [&_svg]:h-full [&_svg]:w-full">
            {!! file_get_contents(base_path('packages/integrations/News/resources/assets/images/ugm-logo.svg')) !!}
        </span>
        <nav aria-label="Navegación principal" class="h-full md:absolute md:inset-y-0 md:left-1/2 md:-translate-x-1/2">
            <a href="{{ route('news.index') }}" aria-current="page"
                class="bg-news-panel focus-visible:outline-news-accent inline-flex h-full items-center px-[13px] text-[13px] font-medium leading-[20px] text-white focus-visible:outline-2 focus-visible:outline-offset-[-2px]">Noticias</a>
        </nav>
    </div>
</header>
