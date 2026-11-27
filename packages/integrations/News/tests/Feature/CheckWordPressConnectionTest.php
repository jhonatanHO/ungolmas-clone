<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Verifies the local diagnostic command without contacting production.
 */
final class CheckWordPressConnectionTest extends TestCase
{
    /**
     * Configures a fake API and isolated cache for each command execution.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'news.enabled' => true,
            'news.wordpress.base_url' => 'https://wordpress.test',
            'news.wordpress.retries' => 0,
            'news.cache.store' => 'array',
        ]);
        Http::preventStrayRequests();
    }

    /**
     * Checks all supported read filters and reuses cached responses on a second run.
     */
    #[Test]
    public function checksReadEndpointsWithoutPostingOrRenderingViews(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);

        foreach ([1, 2] as $run) {
            $this->artisan('news:check-connection')
                ->expectsOutput('categories: OK (0 resultados)')
                ->expectsOutput('posts: OK (0 resultados)')
                ->expectsOutput('featured: OK (0 resultados)')
                ->expectsOutput('popular: OK (0 resultados)')
                ->assertSuccessful();
        }

        Http::assertSentCount(4);
    }

    /**
     * Reports a blocked API without exposing its response body or claiming success.
     */
    #[Test]
    public function reportsConnectionFailureWithANonzeroExitCode(): void
    {
        Http::fake(['wordpress.test/*' => Http::response(['private' => 'not-for-output'], 400)]);

        $this->artisan('news:check-connection')
            ->expectsOutput('No se pudo consultar la API de noticias.')
            ->assertFailed();

        Http::assertSentCount(1);
    }
}
