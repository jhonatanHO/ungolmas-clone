@extends('admin::layouts.auth')

@section('title', 'Recuperar contraseña | CMSNET')

@section('content')
    <a href="{{ route('admin.login') }}"
        class="text-theme-500 hover:text-theme-900 focus-visible:outline-theme-500 absolute left-6 top-8 z-10 inline-flex items-center gap-1.5 rounded text-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 sm:left-8">
        <svg class="size-3" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="m10 4-4 4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Volver al login
    </a>

    <main class="relative z-10 min-h-[650px] w-full max-w-md pt-8">
        <div class="mb-6 text-center">
            <a href="{{ route('admin.login') }}" aria-label="Volver al inicio de sesión"
                class="focus-visible:outline-theme-500 mx-auto flex h-12 w-56 max-w-full items-center justify-center rounded focus-visible:outline-2 focus-visible:outline-offset-4">
                <img src="{{ asset('images/admin-logo.png') }}" alt="UNGOLMAS" width="1080" height="186"
                    class="block h-auto w-full object-contain">
            </a>

            <h1 class="mt-6 text-balance text-[28px] font-medium leading-[34px] tracking-[-0.045rem]">Recuperar contraseña
            </h1>
            <p class="text-theme-500 mt-3 text-balance text-sm leading-5">
                Ingresa el correo electrónico asociado a tu cuenta y te enviaremos instrucciones para recuperar tu
                contraseña.
            </p>
        </div>

        <div class="w-full">
            <label for="email" class="text-theme-500 mb-2 block text-sm leading-5">Correo electrónico</label>
            <input id="email" name="email" type="email" autocomplete="email" placeholder="nombre@ejemplo.com"
                class="border-theme-900/5 bg-linear-[104deg] from-theme-900/5 to-theme-500/10 text-theme-900 placeholder:text-theme-500 focus-visible:ring-theme-900/20 focus-visible:outline-hidden h-12 w-full appearance-none rounded-2xl border-2 from-5% to-100% bg-origin-border px-4 text-base backdrop-blur-[25px] transition duration-200 ease-in-out focus-visible:ring-1">

            <button type="button"
                class="border-theme-900/5 bg-linear-[104deg] from-theme-900/5 to-theme-500/10 text-theme-900 hover:bg-theme-900/90 hover:text-theme-50 focus-visible:bg-theme-900/90 focus-visible:text-theme-50 focus-visible:ring-theme-900/30 focus-visible:outline-hidden mt-6 flex h-12 w-full cursor-pointer items-center justify-center rounded-2xl border-2 from-5% to-100% bg-origin-border px-5 text-sm font-semibold shadow-sm backdrop-blur-[25px] transition-all duration-200 focus-visible:ring-4">
                Enviar instrucciones
            </button>
        </div>
    </main>

    <div role="status" aria-live="polite"
        class="bg-theme-50 fixed bottom-4 left-4 right-4 z-20 hidden items-start gap-4 rounded-2xl border border-emerald-500/30 p-4 shadow-lg sm:bottom-6 sm:left-auto sm:right-6 sm:w-[360px]">
        <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5" />
            <path d="m6.5 10 2.25 2.25 4.75-4.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        <div>
            <p class="text-theme-900 text-sm font-semibold">Revisa tu correo</p>
            <p class="text-theme-500 mt-1 text-xs leading-5">Te enviamos las instrucciones para recuperar tu contraseña.</p>
        </div>
    </div>
@endsection
