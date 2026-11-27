<footer class="text-news-muted py-[30px] text-center text-[12px] leading-[18px]">
    <div class="mx-auto max-w-[1300px] px-5">
        <x-news::official-channels class="mb-6" />
    </div>
    <div class="mx-auto flex max-w-[1300px] flex-col items-center gap-[.9rem] px-5 py-6 text-base leading-6 text-white">
        <span>Powered by</span>
        <img src="{{ Vite::asset('packages/integrations/News/resources/assets/images/lnm-and-combativa.svg') }}"
            alt="LNM &amp; Combativa" width="260" loading="lazy" decoding="async" class="h-auto w-[260px] max-w-full">
    </div>
</footer>
