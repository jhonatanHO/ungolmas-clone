<?php

declare(strict_types=1);

namespace Ugm\WebApp\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for UNGOLMAS WebApp Module.
 *
 * Handles view loading, translations, configuration, and web routing
 * for the user-facing web application.
 */
final class WebAppServiceProvider extends ServiceProvider
{
    /**
     * Register any package services or configurations.
     */
    public function register(): void
    {
        $configFile = __DIR__.'/../../config/config.php';
        if (file_exists($configFile)) {
            $this->mergeConfigFrom($configFile, 'webapp');
        }
    }

    /**
     * Bootstrap views, translations, and routes.
     */
    public function boot(): void
    {
        // 1. Cargar traducciones desde Resources/lang (alias: webapp)
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'webapp');

        // 2. Cargar vistas desde Resources/views (alias: webapp)
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'webapp');

        // 3. Registrar rutas web
        $this->registerRoutes();
    }

    /**
     * Register route groups for the web context.
     */
    protected function registerRoutes(): void
    {
        $webRoutes = __DIR__.'/../../routes/web.php';
        if (file_exists($webRoutes)) {
            Route::middleware('web')->group($webRoutes);
        }
    }
}
