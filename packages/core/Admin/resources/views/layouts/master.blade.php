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

            document.documentElement.setAttribute('data-theme', theme === 'system' ? (isDark ? 'cmsnet-dark' : 'cmsnet-light') : theme);
        })();
    </script>

    @vite(['packages/core/Admin/resources/assets/css/admin.css', 'packages/core/Admin/resources/assets/js/app.ts'])
</head>
<body class="h-full bg-theme-50 text-theme-900 antialiased font-sans flex flex-col md:flex-row transition-colors duration-150">

    <!-- Sidebar Principal CMSNET -->
    <aside class="w-full md:w-64 bg-theme-100 border-r border-theme-300 flex flex-col justify-between p-4 shrink-0">
        <div class="flex flex-col gap-6">
            <!-- Brand Header -->
            <div class="flex items-center justify-between pb-4 border-b border-theme-300">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-theme-900 text-theme-50 flex items-center justify-center font-bold text-xs">
                        U
                    </div>
                    <div>
                        <span class="font-bold text-sm tracking-wide text-theme-900">CMSNET</span>
                        <span class="block text-[10px] text-theme-500 font-mono">UNGOLMAS Core</span>
                    </div>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="flex flex-col gap-1 text-xs">
                <a href="{{ url('/cmsnet') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-theme-200 text-theme-900 font-medium">
                    <svg class="w-4 h-4 text-theme-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ url('/webapp') }}" target="_blank" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-theme-500 hover:text-theme-900 hover:bg-theme-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Ver WebApp Pública
                </a>
            </nav>
        </div>

        <!-- Selector de Apariencia en el Footer del Sidebar -->
        <div class="pt-4 border-t border-theme-300">
            <span class="block text-[11px] font-medium text-theme-500 mb-2">Apariencia CMSNET</span>
            <div class="flex items-center gap-1 bg-theme-200 p-1 rounded-lg border border-theme-300 text-xs">
                <button data-theme-btn="cmsnet-light" title="Modo Claro" class="flex-1 py-1 flex items-center justify-center rounded transition-colors text-theme-500" aria-label="Modo Claro">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <button data-theme-btn="system" title="Tema del Sistema" class="flex-1 py-1 flex items-center justify-center rounded transition-colors text-theme-500" aria-label="Tema del Sistema">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </button>
                <button data-theme-btn="cmsnet-dark" title="Modo Oscuro" class="flex-1 py-1 flex items-center justify-center rounded transition-colors text-theme-500" aria-label="Modo Oscuro">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                <button data-theme-btn="cmsnet-midnight" title="Modo Midnight (Navy)" class="flex-1 py-1 flex items-center justify-center rounded transition-colors text-theme-500" aria-label="Modo Midnight">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.286L13 21l-2.286-6.857L5 12l5.714-2.286L13 3z"/>
                    </svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <main class="flex-1 overflow-y-auto p-6 md:p-8">
        @yield('content')
    </main>

</body>
</html>
