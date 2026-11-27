<nav {{ $attributes->class(['relative flex flex-col items-center gap-[5px]']) }} aria-label="Compartir noticia"
    data-news-share>
    <span class="sr-only">Compartir</span>
    @foreach ($links as $link)
        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" title="Compartir en {{ $link['name'] }}"
            aria-label="Compartir en {{ $link['name'] }}" @class([
                'border-news-border bg-news-panel focus-visible:outline-news-accent flex size-10 items-center justify-center rounded-full border text-white transition duration-200 hover:scale-110 focus-visible:outline-2 focus-visible:outline-offset-2 motion-reduce:transform-none',
                'hover:border-news-facebook hover:bg-news-facebook' =>
                    $link['name'] === 'Facebook',
                'hover:bg-black' => $link['name'] === 'X',
                'hover:border-news-whatsapp hover:bg-news-whatsapp' =>
                    $link['name'] === 'WhatsApp',
                'hover:border-news-telegram hover:bg-news-telegram' =>
                    $link['name'] === 'Telegram',
            ])>
            <svg viewBox="0 0 24 24" class="size-[18px] fill-current" aria-hidden="true">
                @switch($link['name'])
                    @case('Facebook')
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    @break

                    @case('X')
                        <path
                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                    @break

                    @case('WhatsApp')
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.457L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.725 1.451 5.486.002 9.95-4.458 9.954-9.94.002-2.656-1.033-5.153-2.915-7.036C16.529 1.748 14.032.75 11.378.75c-5.49 0-9.956 4.46-9.96 9.943-.002 1.957.513 3.866 1.493 5.568L1.9 22.093l6.027-1.579c1.61.88 3.2 1.34 4.72 1.34zm9.956-6.843c-.27-.135-1.602-.79-1.85-.88-.25-.09-.432-.135-.612.135-.18.27-.7.88-.857 1.06-.157.18-.315.2-.585.065-.27-.135-1.138-.42-2.167-1.34-.8-.713-1.34-1.594-1.498-1.864-.157-.27-.017-.417.118-.552.12-.12.27-.315.405-.47.135-.16.18-.27.27-.45.09-.18.045-.337-.02-.47-.067-.135-.612-1.474-.838-2.017-.22-.53-.44-.457-.612-.466-.157-.008-.337-.01-.517-.01-.18 0-.472.067-.72.337-.247.27-.945.923-.945 2.25 0 1.327.967 2.61 1.102 2.79.135.18 1.902 2.904 4.6 4.066.643.277 1.144.442 1.534.566.645.205 1.233.176 1.698.107.518-.077 1.602-.656 1.83-1.282.226-.625.226-1.16.157-1.272-.07-.113-.25-.18-.52-.315z" />
                    @break

                    @case('Telegram')
                        <path
                            d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L6.877 12.9l-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.46c.536-.194 1.006.128.82.934z" />
                    @break
                @endswitch
            </svg>
        </a>
    @endforeach
    <button type="button" data-news-copy aria-label="Copiar enlace" title="Copiar enlace"
        class="border-news-border bg-news-panel hover:border-news-accent hover:bg-news-accent focus-visible:outline-news-accent flex size-10 cursor-pointer items-center justify-center rounded-full border text-white transition hover:text-black focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50">
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"
            aria-hidden="true">
            <rect x="8" y="8" width="12" height="12" rx="2" />
            <path d="M16 8V4H4v12h4" />
        </svg>
    </button>
    <input data-news-share-url type="text" readonly value="{{ $shareUrl }}" aria-label="Enlace de la noticia"
        class="border-news-border bg-news-base hidden w-40 border p-2 text-[12px] text-white">
    <span data-news-share-feedback role="status" aria-live="polite"
        class="bg-news-base border-news-border absolute -bottom-10 left-1/2 max-w-40 -translate-x-1/2 border px-3 py-[6px] text-center text-[12px] text-white empty:hidden"></span>
    <noscript>
        <p class="text-news-body text-[12px]">Puedes copiar el enlace desde la barra del navegador.</p>
    </noscript>
</nav>
