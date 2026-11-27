<?php

use Illuminate\Support\Facades\Route;
use Ugm\News\Http\Controllers\NewsController;
use Ugm\News\Http\Middleware\HeadlessNews;

/* PUBLIC NEWS ROUTES */

Route::middleware(HeadlessNews::class)->group(function (): void {
    Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
    Route::get('/noticias/{category_slug}', [NewsController::class, 'category'])->name('news.category');
    Route::get('/noticia/{category_slug}/{slug}', [NewsController::class, 'show'])->name('news.post');
});

Route::get('/noticias/{category_slug}/{slug}', [NewsController::class, 'legacy'])->name('news.post.legacy');
