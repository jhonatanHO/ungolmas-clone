<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CMSNET | UNGOLMAS Core Admin')</title>

    <!-- Script Anti-Flicker Inmediato (Previene destellos antes de pintar) -->
    <script>
        (function() {
            let theme = localStorage.getItem('cmsnet_theme') || 'system';
            if (theme === 'dark') theme = 'cmsnet-dark';
            if (theme === 'light') theme = 'cmsnet-light';
            if (theme === 'midnight') theme = 'cmsnet-midnight';

            const isDark = theme === 'cmsnet-dark' || theme === 'cmsnet-midnight' ||
                (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            document.documentElement.classList.remove('cmsnet-dark', 'cmsnet-light', 'cmsnet-midnight', 'dark');

            if (theme === 'cmsnet-midnight') {
                document.documentElement.classList.add('cmsnet-midnight');
            } else if (isDark) {
                document.documentElement.classList.add('cmsnet-dark');
            } else {
                document.documentElement.classList.add('cmsnet-light');
            }

            document.documentElement.setAttribute('data-theme', theme === 'system' ? (isDark ? 'cmsnet-dark' :
                'cmsnet-light') : theme);
        })();
    </script>

    @vite(['packages/core/Admin/resources/assets/css/admin.css', 'packages/core/Admin/resources/assets/js/app.ts'])
</head>

<body data-admin-dashboard
    class="bg-theme-50 text-theme-900 flex h-dvh flex-col overflow-clip font-sans antialiased transition-colors duration-150 md:flex-row">

    <header
        class="border-theme-300 bg-theme-100 z-30 flex shrink-0 items-center justify-between border-b px-4 py-3 md:hidden">
        <a href="{{ url('/cmsnet') }}" class="inline-flex items-center">
            <img src="{{ asset('images/admin-logo.png') }}" alt="UNGOLMAS" width="1080" height="186"
                class="block h-6 w-auto object-contain">
        </a>
        <button id="mobile-menu-btn" type="button" aria-label="Abrir menú" aria-controls="sidebar"
            aria-expanded="false" class="text-theme-500 hover:bg-theme-200 rounded-lg p-1.5">
            <svg class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </header>
    <button id="mobile-backdrop" type="button" tabindex="-1" aria-label="Cerrar menú"
        class="backdrop-blur-xs fixed inset-0 z-40 hidden bg-black/40 md:hidden"></button>

    <aside id="sidebar" data-collapsed="false"
        class="group/sidebar border-theme-300 bg-theme-100 fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 -translate-x-full flex-col justify-between gap-4 overflow-y-auto overflow-x-hidden overscroll-y-contain border-r p-3.5 transition-[width,padding,translate] duration-200 md:relative md:h-full md:translate-x-0 md:data-[collapsed=true]:w-[68px] md:data-[collapsed=true]:px-2">
        <div class="flex flex-col gap-3">
            <div
                class="flex h-11 shrink-0 items-center justify-between px-1 py-0.5 md:group-data-[collapsed=true]/sidebar:justify-center">
                <a href="{{ url('/cmsnet') }}"
                    class="flex min-w-0 items-center md:group-data-[collapsed=true]/sidebar:hidden">
                    <img src="{{ asset('images/admin-logo.png') }}" alt="UNGOLMAS" width="1080" height="186"
                        class="block h-7 w-auto max-w-[172px] object-contain">
                </a>
                <button id="sidebar-toggle-btn" type="button" title="Contraer lateral" aria-label="Contraer lateral"
                    aria-controls="sidebar" aria-expanded="true"
                    class="text-theme-900 hover:bg-theme-200 hidden rounded-md p-1.5 md:inline-flex">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.25"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M9 3v18" />
                    </svg>
                </button>
                <button id="mobile-close-btn" type="button" aria-label="Cerrar menú"
                    class="text-theme-500 rounded-md p-1 md:hidden">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="flex flex-col gap-0.5 text-sm" aria-label="Navegación principal">
                <a href="{{ url('/cmsnet') }}" data-demo-page="Overview" aria-current="page" title="Overview"
                    class="text-theme-700 hover:bg-theme-200/70 hover:text-theme-900 aria-[current=page]:bg-theme-200 aria-[current=page]:text-theme-900 flex min-h-8 items-center gap-2.5 rounded-lg px-2.5 py-1.5 font-medium aria-[current=page]:font-semibold md:group-data-[collapsed=true]/sidebar:mx-auto md:group-data-[collapsed=true]/sidebar:size-8 md:group-data-[collapsed=true]/sidebar:justify-center md:group-data-[collapsed=true]/sidebar:p-0">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10ZM9 21v-7h6v7" />
                    </svg>
                    <span class="md:group-data-[collapsed=true]/sidebar:hidden">Overview</span>
                </a>
                <div
                    class="text-theme-700 whitespace-nowrap px-2.5 pb-0.5 pt-2.5 text-[11px] font-medium uppercase tracking-wide md:group-data-[collapsed=true]/sidebar:invisible">
                    Módulo</div>
                @foreach ([
        'Usuario' => ['M20 21v-2a7 7 0 0 0-7-7h-2a7 7 0 0 0-7 7v2', 'M16 6a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z'],
        'Configuraciones' => ['M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 0 0 2.572-1.065Z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        'Juego' => ['M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z', 'M8 8h.01M16 8h.01M12 12h.01M8 16h.01M16 16h.01'],
        'Integraciones' => ['M15 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 19a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm14 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z', 'M12 8v4M5 16v-4h14v4'],
    ] as $label => $iconPaths)
                    <button type="button" data-demo-page="{{ $label }}" title="{{ $label }} · Demo"
                        class="text-theme-700 hover:bg-theme-200/70 hover:text-theme-900 aria-[current=page]:bg-theme-200 aria-[current=page]:text-theme-900 flex min-h-8 items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left font-medium transition-colors aria-[current=page]:font-semibold md:group-data-[collapsed=true]/sidebar:mx-auto md:group-data-[collapsed=true]/sidebar:size-8 md:group-data-[collapsed=true]/sidebar:justify-center md:group-data-[collapsed=true]/sidebar:p-0">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            @foreach ($iconPaths as $iconPath)
                                <path d="{{ $iconPath }}" />
                            @endforeach
                        </svg>
                        <span class="md:group-data-[collapsed=true]/sidebar:hidden">{{ $label }}</span>
                    </button>
                @endforeach
            </nav>
        </div>

        <div class="flex flex-col gap-2 pt-4">
            <dl aria-label="Totales de la plataforma"
                class="border-theme-300 bg-theme-200/30 flex flex-col gap-2.5 whitespace-nowrap rounded-xl border p-3 md:group-data-[collapsed=true]/sidebar:invisible">
                {{-- NOTE: Usuarios proviene del listado; juegos continúa como valor de demostración. --}}
                @foreach (['Total usuarios' => $totalUsers ?? 0, 'Total juegos' => 0] as $label => $value)
                    <div class="text-theme-500 flex items-center justify-between text-xs">
                        <dt>{{ $label }}</dt>
                        <dd class="text-theme-700 font-medium">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
            <nav class="flex flex-col gap-0.5 text-sm" aria-label="Recursos">
                <button type="button" data-demo-page="Documentación" title="Documentación · Demo"
                    class="text-theme-700 hover:bg-theme-200/70 hover:text-theme-900 aria-[current=page]:bg-theme-200 aria-[current=page]:text-theme-900 flex min-h-8 items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left font-medium aria-[current=page]:font-semibold md:group-data-[collapsed=true]/sidebar:mx-auto md:group-data-[collapsed=true]/sidebar:size-8 md:group-data-[collapsed=true]/sidebar:justify-center md:group-data-[collapsed=true]/sidebar:p-0">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            d="M12 7v14m0-14C9.5 4.5 5.5 4 2 5v15c3.5-1 7.5-.5 10 1m0-14c2.5-2.5 6.5-3 10-2v15c-3.5-1-7.5-.5-10 1" />
                    </svg>
                    <span class="md:group-data-[collapsed=true]/sidebar:hidden">Documentación</span>
                </button>
            </nav>

            <details id="user-profile" data-sidebar-menu class="border-theme-300 relative border-t pt-2">
                <summary title="Administrador"
                    class="hover:bg-theme-200 flex min-h-12 cursor-pointer list-none items-center gap-2.5 rounded-lg p-1.5 md:group-data-[collapsed=true]/sidebar:justify-center">
                    <span
                        class="border-theme-300 bg-theme-200 text-theme-700 flex size-8 shrink-0 items-center justify-center rounded-full border text-xs font-semibold">AD</span>
                    <span class="min-w-0 md:group-data-[collapsed=true]/sidebar:hidden"><span
                            class="block truncate text-sm font-medium">Administrador</span><span
                            class="text-theme-500 block truncate text-xs">admin@example.com</span></span>
                    <svg class="text-theme-400 ml-auto size-4 shrink-0 md:group-data-[collapsed=true]/sidebar:hidden"
                        fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                        stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m8 9 4-4 4 4m-8 6 4 4 4-4" />
                    </svg>
                </summary>
                <div
                    class="border-theme-300 bg-theme-100 absolute bottom-full left-0 z-50 mb-2 w-full rounded-lg border p-1.5 shadow-lg">
                    <button type="button" data-demo-page="Account settings"
                        class="text-theme-700 hover:bg-theme-200 hover:text-theme-900 flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 0 0 2.572-1.065Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        Account settings
                    </button>
                    <div class="border-theme-300 my-1.5 border-t"></div>
                    <div role="group" aria-label="Tema de la interfaz" class="flex flex-col gap-0.5">
                        <span
                            class="text-theme-500 px-2 py-1 text-[10px] font-semibold uppercase tracking-wider">Theme</span>
                        @foreach ([['value' => 'cmsnet-light', 'label' => 'Light', 'paths' => ['M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z', 'M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42']], ['value' => 'cmsnet-dark', 'label' => 'Dark', 'paths' => ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z']], ['value' => 'cmsnet-midnight', 'label' => 'Midnight', 'paths' => ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z', 'M18 2v6m-3-3h6']], ['value' => 'system', 'label' => 'System', 'paths' => ['M8 21h8m-4-4v4', 'M5 3h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z']]] as $themeOption)
                            <button type="button" data-theme-btn="{{ $themeOption['value'] }}"
                                class="text-theme-500 hover:text-theme-900 aria-pressed:text-theme-900 group flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm">
                                <svg class="size-3.5 shrink-0 opacity-0 group-aria-pressed:opacity-100" fill="none"
                                    stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                    stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6" />
                                </svg>
                                <svg class="size-4 shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    @foreach ($themeOption['paths'] as $iconPath)
                                        <path d="{{ $iconPath }}" />
                                    @endforeach
                                </svg>
                                {{ $themeOption['label'] }}
                            </button>
                        @endforeach
                    </div>
                    <div class="border-theme-300 my-1.5 border-t"></div>
                    <a href="{{ route('admin.logout') }}"
                        class="text-theme-700 hover:bg-theme-200 hover:text-theme-900 flex items-center gap-2 rounded-md px-2 py-2 text-sm">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m7-13 5 5-5 5m5-5H9" />
                        </svg>
                        Salir
                    </a>
                </div>
            </details>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <main
        class="relative min-h-0 min-w-0 flex-1 overflow-y-auto overflow-x-hidden overscroll-y-contain px-4 py-7 sm:px-6 md:px-10 md:py-9">
        <div class="mx-auto w-full max-w-[1060px]">
            @yield('content')
        </div>
    </main>

</body>

</html>
