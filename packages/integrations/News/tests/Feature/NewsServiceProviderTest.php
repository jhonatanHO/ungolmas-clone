<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;
use Ugm\News\Providers\NewsServiceProvider;

/**
 * Verifies the News package bootstrap without contacting WordPress.
 */
final class NewsServiceProviderTest extends TestCase
{
    /**
     * Prevents external HTTP requests during package bootstrap checks.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    /**
     * Resolves the registered provider from the lowercase package directory.
     */
    #[Test]
    public function loadsTheRegisteredPackageProvider(): void
    {
        self::assertInstanceOf(
            NewsServiceProvider::class,
            $this->app->getProvider(NewsServiceProvider::class),
        );

        self::assertSame(
            realpath(base_path('packages/integrations/News/src/Providers/NewsServiceProvider.php')),
            (new ReflectionClass(NewsServiceProvider::class))->getFileName(),
        );

        self::assertTrue(config()->has('news.enabled'));
        Http::assertNothingSent();
    }

    /**
     * Preserves explicit application configuration when registering defaults.
     */
    #[Test]
    public function preservesTheConfiguredActivationFlag(): void
    {
        config()->set('news.enabled', false);

        (new NewsServiceProvider($this->app))->register();

        self::assertFalse(config('news.enabled'));
        Http::assertNothingSent();
    }

    /**
     * Registers package views under an isolated namespace when enabled.
     */
    #[Test]
    public function registersTheNewsViewNamespace(): void
    {
        config()->set('news.enabled', true);

        (new NewsServiceProvider($this->app))->boot();

        $finder = $this->app['view']->getFinder();
        self::assertInstanceOf(FileViewFinder::class, $finder);
        $hints = $finder->getHints();
        $paths = array_map(
            static fn (string $path): string|false => realpath($path),
            $hints['news'] ?? [],
        );

        self::assertContains(
            realpath(base_path('packages/integrations/News/resources/views')),
            $paths,
        );

        Http::assertNothingSent();
    }

    /**
     * Skips route and view registration when the package is disabled.
     */
    #[Test]
    public function skipsBootstrappingWhenDisabled(): void
    {
        config()->set('news.enabled', false);
        $finder = $this->app['view']->getFinder();
        self::assertInstanceOf(FileViewFinder::class, $finder);
        $finder->replaceNamespace('news', []);

        Route::shouldReceive('middleware')->never();
        Route::shouldReceive('prefix')->never();

        (new NewsServiceProvider($this->app))->boot();

        self::assertSame([], $finder->getHints()['news']);
        Http::assertNothingSent();
    }
}
