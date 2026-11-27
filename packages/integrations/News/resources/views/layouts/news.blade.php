<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth motion-reduce:scroll-auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="theme-color" content="{{ config('news.theme_color') }}">
    <link rel="canonical" href="{{ $seo['url'] }}">
    @if (request()->routeIs('news.post'))
        <meta property="og:type" content="article">
    @else
        <meta property="og:type" content="website">
    @endif
    <meta property="og:site_name" content="UNGOLMAS">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['url'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    @if ($seo['image'])
        <meta property="og:image" content="{{ $seo['image'] }}">
        <meta name="twitter:image" content="{{ $seo['image'] }}">
    @endif
    @if (isset($post['author']))
        <meta name="author" content="{{ $post['author'] }}">
    @endif
    @if (isset($unavailable))
        <meta name="robots" content="noindex,follow">
    @endif
    @vite(['packages/integrations/News/resources/assets/css/app.css', 'packages/integrations/News/resources/assets/js/app.ts'])
</head>

<body id="inicio"
    class="bg-news-base bg-news-page font-news-copy selection:bg-news-accent flex min-h-full flex-col pt-[49px] text-base font-medium text-white selection:text-black md:pt-[46px]">
    <a href="#contenido"
        class="bg-news-accent sr-only z-50 rounded-lg p-3 font-semibold text-slate-950 focus:not-sr-only focus:fixed focus:left-2 focus:top-2">Saltar
        al contenido</a>
    @include('news::partials.header')
    @unless (request()->routeIs('news.post'))
        <x-news::news-nav-categories :wp-categories="$wpCategories" />
    @endunless
    <main id="contenido" class="w-full flex-1" tabindex="-1">
        @yield('content')
    </main>
    @include('news::partials.footer')
</body>

</html>
