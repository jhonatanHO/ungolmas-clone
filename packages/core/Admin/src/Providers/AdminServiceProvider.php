<?php

declare(strict_types=1);

namespace Ugm\Admin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for UNGOLMAS Admin Module.
 *
 * Handles dependency injection, configuration merging, migration loading,
 * view publishing, and route registration for this modular package.
 */
final class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any application or package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'admin'
        );
    }

    /**
     * Bootstrap any package services, views, migrations, and routes.
     */
    public function boot(): void
    {
        // Load database migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'admin');

        // Register package routes
        $this->registerRoutes();
    }

    /**
     * Register route groups for web, api, and admin contexts.
     */
    protected function registerRoutes(): void
    {
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
            $adminPrefix = config('app.admin_url', 'cmsnet');
            Route::prefix($adminPrefix)->middleware(['web'])->group($adminRoutes);
        }
    }
}
