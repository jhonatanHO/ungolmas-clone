@extends('admin::layouts.master')

@section('title', 'Dashboard | CMSNET')

@section('content')
    <div id="dashboard-overview" class="@container space-y-5">
        <header>
            <h1 class="text-theme-900 text-2xl font-bold tracking-tight">Usuarios</h1>
            <p class="text-theme-500 mt-2 text-sm">{{ $users->total() }} usuarios registrados · Más recientes primero</p>
        </header>

        <div>
            <div class="relative min-w-0 flex-1">
                <div class="text-theme-400 pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                        stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="7.5" />
                        <path d="m16 16 5 5" />
                    </svg>
                </div>
                <input type="search" aria-label="Buscar usuarios (demostración)" placeholder="Buscar usuarios (demo)..."
                    aria-describedby="user-list-note"
                    class="border-theme-400 bg-theme-100 text-theme-900 placeholder:text-theme-500 focus:outline-hidden focus:ring-theme-400 h-8 w-full rounded-md border pl-9 pr-3 text-sm focus:ring-1">
            </div>

            <p id="user-list-note" class="text-theme-500 mt-2 text-xs">Listado de solo lectura. La búsqueda continúa como
                demostración.</p>
        </div>

        <div class="border-theme-300/80 bg-theme-100 shadow-2xs relative overflow-hidden rounded-lg border">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse text-left text-sm">
                    <caption class="sr-only">Usuarios registrados en la base de datos local</caption>
                    <thead>
                        <tr class="border-theme-300 bg-theme-200/60 text-theme-500 h-8 border-b text-xs">
                            <th scope="col" class="px-3 font-medium">Usuario</th>
                            <th scope="col" class="px-3 font-medium">Nombre completo</th>
                            <th scope="col" class="px-3 font-medium">Correo</th>
                            <th scope="col" class="px-3 font-medium">Tipo</th>
                            <th scope="col" class="px-3 font-medium">Estado</th>
                            <th scope="col" class="px-3 text-right font-medium">Registro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-theme-300/60 text-theme-700 divide-y">
                        @forelse ($users as $user)
                            <tr data-user-row class="hover:bg-theme-200/60 h-10 transition-colors">
                                <td class="px-3 py-1.5 align-middle">
                                    <span
                                        class="text-theme-900 block whitespace-nowrap font-medium">{{ $user->user }}</span>
                                    <span class="text-theme-500 text-xs">#{{ $user->id }}</span>
                                </td>
                                <td class="max-w-56 truncate px-3 py-1.5 align-middle">
                                    {{ collect([$user->name, $user->first_lastname, $user->second_lastname])->filter()->implode(' ') ?:'Sin nombre' }}
                                </td>
                                <td class="max-w-64 truncate px-3 py-1.5 align-middle">{{ $user->email }}</td>
                                <td class="whitespace-nowrap px-3 py-1.5 align-middle">{{ $user->user_type ?? 'Sin tipo' }}
                                </td>
                                <td class="px-3 py-1.5 align-middle">
                                    <span
                                        class="border-theme-300 bg-theme-200 text-theme-700 inline-flex whitespace-nowrap rounded-full border px-2 py-0.5 text-xs">{{ $user->user_state ?? 'Sin estado' }}</span>
                                </td>
                                <td class="text-theme-500 whitespace-nowrap px-3 py-1.5 text-right align-middle">
                                    {{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-theme-500 px-3 py-10 text-center">No hay usuarios
                                    registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <nav aria-label="Paginación de usuarios"
                class="border-theme-300 text-theme-500 flex flex-wrap items-center justify-between gap-3 border-t px-3 py-3 text-xs">
                <p>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios</p>
                <div class="flex items-center gap-2">
                    @if ($users->previousPageUrl())
                        <a href="{{ $users->previousPageUrl() }}" rel="prev"
                            class="border-theme-400 hover:bg-theme-200 text-theme-700 inline-flex h-8 items-center rounded-md border px-2.5">Anterior</a>
                    @else
                        <span aria-disabled="true"
                            class="border-theme-300 inline-flex h-8 items-center rounded-md border px-2.5 opacity-50">Anterior</span>
                    @endif
                    @if ($users->nextPageUrl())
                        <a href="{{ $users->nextPageUrl() }}" rel="next"
                            class="border-theme-400 hover:bg-theme-200 text-theme-700 inline-flex h-8 items-center rounded-md border px-2.5">Siguiente</a>
                    @else
                        <span aria-disabled="true"
                            class="border-theme-300 inline-flex h-8 items-center rounded-md border px-2.5 opacity-50">Siguiente</span>
                    @endif
                </div>
            </nav>
        </div>
    </div>
    <section id="dashboard-preview" class="hidden space-y-6" aria-labelledby="dashboard-preview-title">
        <h1 id="dashboard-preview-title" tabindex="-1" class="text-theme-900 text-2xl font-bold tracking-tight"></h1>
        <div class="border-theme-300 bg-theme-100 rounded-xl border p-6">
            <p id="dashboard-preview-description" class="text-theme-700 text-sm leading-relaxed"></p>
            <p class="text-theme-500 mt-4 text-xs">Vista de demostración. Esta sección todavía no realiza operaciones
                reales.</p>
        </div>
        @include('admin::partials.demo-forms')
    </section>
@endsection
