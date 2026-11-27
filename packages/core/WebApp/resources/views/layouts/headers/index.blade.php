<header class="border-b border-ugm-border bg-ugm-card/70 backdrop-blur-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="{{ url('/webapp') }}" class="flex items-center gap-2">
            <img src="https://ungolmas.com/assets/img/ugm-logo-pc.svg" width="140" height="28" alt="UNGOLMAS" class="h-7 w-auto object-contain">
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ url('/cmsnet') }}" target="_blank" class="text-xs text-ugm-muted hover:text-ugm-text font-medium transition-colors hidden sm:inline-block">
                Panel CMSNET &rarr;
            </a>

            <!-- Toggle de Tema WebApp (Sol / Luna) -->
            <button data-ugm-theme-toggle title="Alternar Modo Oscuro / Claro" class="p-2 rounded-lg border border-ugm-border bg-ugm-bg text-ugm-muted hover:text-ugm-text hover:border-ugm-primary/40 transition-colors flex items-center gap-2 text-xs cursor-pointer">
                <!-- Icono Sol (visible en modo oscuro) -->
                <svg class="w-4 h-4 hidden [data-theme='ungolmas-dark']_&]:block [.ungolmas-dark_&]:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <!-- Icono Luna (visible en modo claro) -->
                <svg class="w-4 h-4 hidden [data-theme='ungolmas-light']_&]:block [.ungolmas-light_&]:block text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <span data-ugm-theme-label class="font-medium">Modo Oscuro</span>
            </button>
        </div>
    </div>
</header>
