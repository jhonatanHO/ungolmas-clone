@props([
    'hasHeader' => true,
    'hasFooter' => true,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UNGOLMAS - Portal Web de Gaming y Torneos')</title>

    <!-- Script Anti-Flicker Inmediato (Por defecto SIEMPRE ungolmas-dark) -->
    <script>
        (function() {
            let theme = localStorage.getItem('ugm_theme') || 'ungolmas-dark';
            if (theme !== 'ungolmas-dark' && theme !== 'ungolmas-light') {
                theme = 'ungolmas-dark';
            }

            document.documentElement.classList.remove('ungolmas-dark', 'ungolmas-light', 'dark');

            if (theme === 'ungolmas-light') {
                document.documentElement.classList.add('ungolmas-light');
                document.documentElement.setAttribute('data-theme', 'ungolmas-light');
            } else {
                document.documentElement.classList.add('ungolmas-dark', 'dark');
                document.documentElement.setAttribute('data-theme', 'ungolmas-dark');
            }
        })();
    </script>

    @vite(['packages/core/WebApp/resources/assets/css/webapp.css', 'packages/core/WebApp/resources/assets/js/app.ts'])
</head>

<body class="min-h-full bg-ugm-bg text-ugm-text antialiased transition-colors duration-150 flex flex-col justify-between">

    @if ($hasHeader)
        @include('webapp::layouts.headers.index')
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    @if ($hasFooter)
        @include('webapp::layouts.footer.index')
    @endif

</body>

</html>
