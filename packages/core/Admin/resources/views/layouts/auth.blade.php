<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="cmsnet-dark min-h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acceso | CMSNET')</title>

    @vite(['packages/core/Admin/resources/assets/css/admin.css', 'packages/core/Admin/resources/assets/js/app.ts'])
</head>

<body
    class="bg-theme-50 text-theme-900 relative isolate flex min-h-dvh items-center justify-center overflow-auto px-4 font-sans antialiased">
    <svg class="pointer-events-none fixed inset-0 -z-10 h-full w-full" viewBox="0 0 1840 833" preserveAspectRatio="none"
        opacity="0.7" aria-hidden="true">
        <defs>
            <radialGradient id="login-glow" cx="30%" cy="76%" r="70%">
                <stop offset="0" class="[stop-color:var(--theme-300)]" stop-opacity="0.48" />
                <stop offset="1" class="[stop-color:var(--theme-50)]" stop-opacity="0" />
            </radialGradient>
            <linearGradient id="login-fold-top" x1="0" y1="1" x2="0.9" y2="0">
                <stop offset="0" class="[stop-color:var(--theme-700)]" stop-opacity="0.82" />
                <stop offset="0.22" class="[stop-color:var(--theme-500)]" stop-opacity="0.85" />
                <stop offset="0.6" class="[stop-color:var(--theme-300)]" />
                <stop offset="1" class="[stop-color:var(--theme-100)]" />
            </linearGradient>
            <linearGradient id="login-fold-bottom" x1="0" y1="0" x2="0.3" y2="1">
                <stop offset="0" class="[stop-color:var(--theme-700)]" stop-opacity="0.85" />
                <stop offset="0.12" class="[stop-color:var(--theme-500)]" />
                <stop offset="0.55" class="[stop-color:var(--theme-300)]" />
                <stop offset="1" class="[stop-color:var(--theme-100)]" />
            </linearGradient>
            <filter id="login-grain">
                <feTurbulence type="fractalNoise" baseFrequency="0.72" numOctaves="3" stitchTiles="stitch" />
                <feColorMatrix type="saturate" values="0" />
            </filter>
        </defs>
        <rect width="1840" height="833" fill="url(#login-glow)" />
        <path d="M1140 0H1840V526C1680 382 1304 312 1140 0Z" fill="url(#login-fold-top)" />
        <path d="M0 478C146 566 357 740 553 833H0Z" fill="url(#login-fold-bottom)" />
        <rect width="1840" height="833" filter="url(#login-grain)" opacity="0.045" />
    </svg>

    @yield('content')
</body>

</html>
