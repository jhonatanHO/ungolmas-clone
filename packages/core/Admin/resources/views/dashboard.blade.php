@extends('admin::layouts.master')

@section('title', 'Dashboard | CMSNET')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col gap-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-theme-300">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-theme-900">Panel Administrativo CMSNET</h1>
            <p class="text-sm text-theme-500 mt-1">Monolito Modular UNGOLMAS Core &middot; Arquitectura Tailwind y Temas Independientes.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Sistema Operacional
            </span>
        </div>
    </div>

    <!-- Indicador y Selector Rápido de Temas -->
    <div class="bg-theme-100 border border-theme-300 rounded-xl p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-theme-900">Temas de CMSNET (Tailwind Independiente)</h2>
                <p class="text-xs text-theme-500 mt-0.5">Detecta automáticamente el modo del sistema operativo por defecto con soporte instantáneo a 3 paletas.</p>
            </div>
            <div class="flex items-center gap-1 bg-theme-200 p-1 rounded-lg border border-theme-300 text-xs shrink-0">
                <button data-theme-btn="cmsnet-light" class="px-3 py-1.5 flex items-center gap-1.5 rounded text-theme-500 hover:text-theme-900 transition-colors font-medium">
                    <span>Claro</span>
                </button>
                <button data-theme-btn="system" class="px-3 py-1.5 flex items-center gap-1.5 rounded text-theme-500 hover:text-theme-900 transition-colors font-medium">
                    <span>Sistema (Auto)</span>
                </button>
                <button data-theme-btn="cmsnet-dark" class="px-3 py-1.5 flex items-center gap-1.5 rounded text-theme-500 hover:text-theme-900 transition-colors font-medium">
                    <span>Dark (Obsidian)</span>
                </button>
                <button data-theme-btn="cmsnet-midnight" class="px-3 py-1.5 flex items-center gap-1.5 rounded text-theme-500 hover:text-theme-900 transition-colors font-medium">
                    <span>Midnight (Navy)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Grid de Métricas y Módulos Core -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-theme-100 border border-theme-300 rounded-xl p-5">
            <span class="text-xs font-medium text-theme-500 uppercase tracking-wider">Módulos Core Activos</span>
            <div class="text-3xl font-extrabold text-theme-900 mt-2">6</div>
            <p class="text-xs text-theme-500 mt-1">Config, User, Admin, Gamification, Game, WebApp</p>
        </div>

        <div class="bg-theme-100 border border-theme-300 rounded-xl p-5">
            <span class="text-xs font-medium text-theme-500 uppercase tracking-wider">Integraciones Satélite</span>
            <div class="text-3xl font-extrabold text-theme-900 mt-2">5</div>
            <p class="text-xs text-theme-500 mt-1">News, Store, Avatar, Ranking, Ads</p>
        </div>

        <div class="bg-theme-100 border border-theme-300 rounded-xl p-5">
            <span class="text-xs font-medium text-theme-500 uppercase tracking-wider">Pipeline de Assets</span>
            <div class="text-3xl font-extrabold text-theme-900 mt-2">Vite 6</div>
            <p class="text-xs text-theme-500 mt-1">Compilación centralizada con Tailwind @config aislado</p>
        </div>
    </div>

    <!-- Muestra de Paleta Adaptativa -->
    <div class="bg-theme-100 border border-theme-300 rounded-xl p-6">
        <h3 class="text-sm font-semibold text-theme-900 mb-4">Escala de Superficies y Contraste Adaptativo (theme-*)</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-9 gap-2 text-center text-xs font-mono">
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-50 text-theme-900">
                <span class="block font-bold">50</span>
                <span class="text-[10px] text-theme-500">Canvas</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-100 text-theme-900">
                <span class="block font-bold">100</span>
                <span class="text-[10px] text-theme-500">Surface</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-200 text-theme-900">
                <span class="block font-bold">200</span>
                <span class="text-[10px] text-theme-500">Subtle</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-300 text-theme-900">
                <span class="block font-bold">300</span>
                <span class="text-[10px] text-theme-500">Border</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-400 text-theme-50">
                <span class="block font-bold">400</span>
                <span class="text-[10px] text-theme-500">Muted</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-500 text-theme-50">
                <span class="block font-bold">500</span>
                <span class="text-[10px] text-theme-100">Secondary</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-700 text-theme-50">
                <span class="block font-bold">700</span>
                <span class="text-[10px] text-theme-200">Body</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-900 text-theme-50">
                <span class="block font-bold">900</span>
                <span class="text-[10px] text-theme-300">Heading</span>
            </div>
            <div class="p-3 rounded-lg border border-theme-300 bg-theme-950 text-theme-50">
                <span class="block font-bold">950</span>
                <span class="text-[10px] text-theme-300">Accent</span>
            </div>
        </div>
    </div>

</div>
@endsection
