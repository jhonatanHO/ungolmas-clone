@extends('webapp::layouts.master')

@section('title', 'Inicio | UNGOLMAS WebApp')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col gap-8">

    <!-- Hero Section -->
    <div class="bg-ugm-card border border-ugm-border rounded-2xl p-8 sm:p-12 relative overflow-hidden">
        <div class="max-w-2xl flex flex-col gap-4 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-ugm-primary/10 text-ugm-primary border border-ugm-primary/20 w-fit">
                ⚽ Plataforma de Pronósticos Deportivos
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-ugm-text font-display">
                Vive la pasión del fútbol con <span class="text-ugm-primary">UNGOLMAS</span>
            </h1>
            <p class="text-sm sm:text-base text-ugm-muted leading-relaxed">
                Participa en torneos deportivos, compite en pollas futboleras, canjea premios reales y escala en el ranking nacional.
            </p>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button data-ugm-theme-toggle class="px-5 py-2.5 rounded-xl bg-ugm-primary hover:opacity-90 text-white text-sm font-semibold transition-all shadow-md cursor-pointer">
                    Alternar Tema (Dark / Light)
                </button>
                <a href="{{ url('/cmsnet') }}" class="px-5 py-2.5 rounded-xl border border-ugm-border bg-ugm-bg hover:bg-ugm-card text-ugm-text text-sm font-semibold transition-colors">
                    Ir al Panel CMSNET
                </a>
            </div>
        </div>
    </div>

    <!-- Grid de Métricas y Características -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-ugm-card border border-ugm-border rounded-xl p-6 flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-ugm-gold/10 text-ugm-gold flex items-center justify-center font-bold">
                🏆
            </div>
            <h2 class="text-base font-bold text-ugm-text mt-2">Doble Economía</h2>
            <p class="text-xs text-ugm-muted leading-relaxed">
                Monedas <span class="text-ugm-gold font-semibold">Coins</span> canjeables en tienda física y virtual, junto con <span class="text-ugm-primary font-semibold">Points</span> competitivos para tablas clasificatorias.
            </p>
        </div>

        <div class="bg-ugm-card border border-ugm-border rounded-xl p-6 flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-ugm-accent/10 text-ugm-accent flex items-center justify-center font-bold">
                ⚡
            </div>
            <h2 class="text-base font-bold text-ugm-text mt-2">Tailwind v3 Independiente</h2>
            <p class="text-xs text-ugm-muted leading-relaxed">
                Tema <span class="text-ugm-accent font-semibold">ungolmas-dark</span> activo por defecto y modo claro <span class="text-ugm-accent font-semibold">ungolmas-light</span>, compilado centralizadamente con Vite.
            </p>
        </div>

        <div class="bg-ugm-card border border-ugm-border rounded-xl p-6 flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">
                📦
            </div>
            <h2 class="text-base font-bold text-ugm-text mt-2">Monolito Modular</h2>
            <p class="text-xs text-ugm-muted leading-relaxed">
                6 módulos Core y 5 integraciones satélite totalmente desacoplados mediante eventos y contratos PSR-4.
            </p>
        </div>
    </div>

</div>
@endsection
