<?php

declare(strict_types=1);

namespace Ugm\News\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ugm\News\Console\Commands\CheckWordPressConnectionCommand;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Services\WordPressService;

/**
 * Registers the News package configuration, views, migrations, and routes.
 */
final class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register any application or package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'news'
        );

        $this->app->bind(NewsRepository::class, WordPressService::class);
    }

    /**
     * Bootstrap any package services, views, migrations, and routes.
     */
    public function boot(): void
    {
        if (! config('news.enabled', true)) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->commands([CheckWordPressConnectionCommand::class]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'news');

        Blade::componentNamespace('Ugm\\News\\View\\Components', 'news');
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/components', 'news');

        $this->registerRoutes();
    }

    /**
     * Register route groups for web, api, and admin contexts.
     */
    protected function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $webRoutes = __DIR__.'/../../routes/web.php';
        if (file_exists($webRoutes)) {
            Route::middleware('web')->group($webRoutes);
        }

        $apiRoutes = __DIR__.'/../../routes/api.php';
        if (file_exists($apiRoutes)) {
            Route::prefix('api')->middleware('api')->group($apiRoutes);
        }

        $adminRoutes = __DIR__.'/../../routes/admin.php';
        if (file_exists($adminRoutes)) {
            $adminPrefix = (string) config('custom.admin_url', 'cmsnet');
            Route::prefix($adminPrefix)->middleware(['web'])->group($adminRoutes);
        }
    }
}
