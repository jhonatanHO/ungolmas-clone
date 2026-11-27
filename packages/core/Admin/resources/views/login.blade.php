@extends('admin::layouts.auth')

@section('title', 'Iniciar sesión | CMSNET')

@section('content')
    <main id="app" class="relative z-10 min-h-[650px] w-full max-w-lg pt-8">
        <div class="mb-6 text-center">
            <a href="{{ url('/') }}" aria-label="Inicio de UNGOLMAS"
                class="focus-visible:outline-theme-500 mx-auto flex h-12 w-56 max-w-full items-center justify-center rounded focus-visible:outline-2 focus-visible:outline-offset-4">
                <img src="{{ asset('images/admin-logo.png') }}" alt="UNGOLMAS" width="1080" height="186"
                    class="block h-auto w-full object-contain">
            </a>

            <h1 class="mt-6 text-balance text-[28px] font-medium leading-[34px] tracking-[-0.045rem]">Inicia sesión en
                CMSNET
            </h1>
            <p class="text-theme-500 mt-3 text-balance text-sm leading-5">
                ¿No tienes una cuenta?
                <button type="button"
                    class="text-theme-900 hover:text-theme-700 font-semibold transition-colors">Regístrate.</button>
            </p>
        </div>

        <button type="button"
            class="border-theme-900/5 bg-linear-[104deg] from-theme-900/5 to-theme-500/10 text-theme-900 hover:bg-theme-900/90 hover:text-theme-50 focus-visible:bg-theme-900/90 focus-visible:text-theme-50 focus-visible:ring-theme-900/30 focus-visible:outline-hidden flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 from-5% to-100% bg-origin-border px-5 text-sm font-semibold shadow-sm backdrop-blur-[25px] transition-all duration-200 focus-visible:ring-4">
            <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M21.6 12.2c0-.7-.1-1.4-.2-2.1H12v4h5.4a4.6 4.6 0 0 1-2 3v2.5h3.2c1.9-1.7 3-4.2 3-7.4ZM12 22c2.7 0 5-.9 6.6-2.4l-3.2-2.5c-.9.6-2 1-3.4 1-2.6 0-4.8-1.8-5.6-4.2H3.1v2.6A10 10 0 0 0 12 22ZM6.4 13.9a6 6 0 0 1 0-3.8V7.5H3.1a10 10 0 0 0 0 9l3.3-2.6ZM12 5.9c1.5 0 2.8.5 3.8 1.5l2.9-2.9A9.6 9.6 0 0 0 12 2a10 10 0 0 0-8.9 5.5l3.3 2.6C7.2 7.7 9.4 5.9 12 5.9Z" />
            </svg>
            Iniciar sesión con Google
        </button>

        <div class="text-theme-500 my-6 flex w-full items-center gap-4 text-sm" aria-hidden="true">
            <span class="bg-theme-300 h-px flex-1"></span>
            o
            <span class="bg-theme-300 h-px flex-1"></span>
        </div>

        <div class="w-full">
            <label for="email" class="text-theme-500 mb-2 block text-sm leading-5">Correo electrónico</label>
            <input id="email" name="email" type="email" autocomplete="username" placeholder="nombre@ejemplo.com"
                class="border-theme-900/5 bg-linear-[104deg] from-theme-900/5 to-theme-500/10 text-theme-900 placeholder:text-theme-500 focus-visible:ring-theme-900/20 focus-visible:outline-hidden h-12 w-full appearance-none rounded-2xl border-2 from-5% to-100% bg-origin-border px-4 text-base backdrop-blur-[25px] transition duration-200 ease-in-out focus-visible:ring-1">

            <div class="mb-2 mt-5 flex flex-wrap items-center justify-between gap-2 text-sm leading-5">
                <label for="password" class="text-theme-500">Contraseña</label>
                <a href="{{ route('admin.password.request') }}"
                    class="text-theme-900 hover:text-theme-700 font-semibold transition-colors">¿Olvidaste tu
                    contraseña?</a>
            </div>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password"
                    placeholder="••••••••••••"
                    class="border-theme-900/5 bg-linear-[104deg] from-theme-900/5 to-theme-500/10 text-theme-900 placeholder:text-theme-500 focus-visible:ring-theme-900/20 focus-visible:outline-hidden h-12 w-full appearance-none rounded-2xl border-2 from-5% to-100% bg-origin-border py-3 pl-4 pr-12 text-base backdrop-blur-[25px] transition duration-200 ease-in-out focus-visible:ring-1">
                <button type="button" aria-label="Mostrar contraseña"
                    class="text-theme-500 hover:text-theme-900 focus-visible:outline-theme-500 absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-2xl focus-visible:outline-2">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor"
                            stroke-width="1.5" />
                        <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                </button>
            </div>

            <button type="button"
                class="border-theme-900/5 bg-linear-[104deg] from-theme-900/5 to-theme-500/10 text-theme-900 hover:bg-theme-900/90 hover:text-theme-50 focus-visible:bg-theme-900/90 focus-visible:text-theme-50 focus-visible:ring-theme-900/30 focus-visible:outline-hidden mt-5 flex h-12 w-full cursor-pointer items-center justify-center rounded-2xl border-2 from-5% to-100% bg-origin-border px-5 text-sm font-semibold shadow-sm backdrop-blur-[25px] transition-all duration-200 focus-visible:ring-4">
                Iniciar sesión
            </button>
        </div>

        <p class="text-theme-500 mt-8 text-center text-xs leading-5">
            Al iniciar sesión, aceptas nuestros
            <button type="button" class="hover:text-theme-900 underline underline-offset-2">Términos</button>
            y nuestra
            <button type="button" class="hover:text-theme-900 underline underline-offset-2">Política de
                privacidad</button>.
        </p>

        <admin-login></admin-login>

    </main>
@endsection
