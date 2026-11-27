<aside aria-label="Publicidad"
    class="sticky top-[60px] hidden w-[180px] shrink-0 space-y-[20px] self-start md:block min-[992px]:w-[250px]">
    @hasSection('news-sidebar')
        @yield('news-sidebar')
    @else
        @foreach ([1, 2, 3] as $slot)
            <div
                class="text-news-muted flex h-[440px] flex-col items-center justify-center text-center text-[12px] leading-[18px]">
                <span>Publicidad</span>
            </div>
        @endforeach
    @endif
</aside>
