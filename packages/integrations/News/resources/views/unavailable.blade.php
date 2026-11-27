@extends('news::layouts.news')

@php($unavailable = true)

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-20 text-center">
        <p class="text-news-accent mb-3 text-sm font-semibold uppercase tracking-widest">Noticias UNGOLMAS</p>
        <h1 class="font-news-display mb-5 text-3xl font-bold">Las noticias no están disponibles temporalmente</h1>
        <p class="text-news-body mb-8 leading-relaxed">No pudimos consultar el servicio de noticias. Inténtalo de nuevo en
            unos minutos.</p>
        <a href="{{ $retryUrl }}"
            class="bg-news-accent hover:bg-news-headline focus-visible:outline-news-accent inline-flex px-6 py-3 font-medium text-black focus-visible:outline-2 focus-visible:outline-offset-4">Volver
            a intentar</a>
    </section>
@endsection
