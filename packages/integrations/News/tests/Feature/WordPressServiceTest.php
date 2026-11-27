<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Feature;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Exceptions\WordPressApiException;

/**
 * Verifies WordPress transport, normalization, and API-only cache behavior offline.
 */
final class WordPressServiceTest extends TestCase
{
    /**
     * Isolates the News cache and prevents accidental external requests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'news.enabled' => true,
            'news.wordpress.base_url' => 'https://wordpress.test',
            'news.wordpress.retries' => 0,
            'news.cache.store' => 'array',
            'news.cache.ttl' => 18000,
        ]);

        Http::preventStrayRequests();
    }

    /**
     * Preserves embedded media, taxonomy, metadata, and Spanish date fields.
     */
    #[Test]
    public function normalizesTheSourcePostContract(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([$this->postPayload()])]);

        $post = $this->news()->request('posts', ['_embed' => 1, 'per_page' => 1])[0];

        self::assertSame(7, $post['id']);
        self::assertSame('Fútbol & noticias', $post['title']);
        self::assertSame('24 de noviembre de 2026', $post['date']);
        self::assertSame('Redacción', $post['author']);
        self::assertSame('https://media.test/full.webp', $post['image']);
        self::assertSame('https://media.test/small.webp', $post['images']['rectangle-md']);
        self::assertSame('deportes', $post['category_slug']);
        self::assertSame([3], $post['categories']);
        self::assertSame('<p>Contenido editorial</p>', $post['contentHtml']);
        self::assertSame('SEO', $post['yoast_head_json']['title']);
        self::assertSame(25, $post['views_count']);
        self::assertTrue($post['is_featured']);

        Http::assertSent(static fn (Request $request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://wordpress.test/wp-json/wp/v2/posts?')
            && $request['_embed'] === 1 && $request['per_page'] === 1);
    }

    /**
     * Tolerates missing optional fields without relying on external fallback images.
     */
    #[Test]
    public function acceptsMinimalPostsAndInvalidOptionalDates(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([['id' => 1, 'slug' => 'minimal', 'date' => 'invalid-date']])]);

        $post = $this->news()->request('posts')[0];

        self::assertSame('Sin título', $post['title']);
        self::assertSame('', $post['date']);
        self::assertSame('', $post['image']);
        self::assertSame([], $post['images']);
        self::assertSame('uncategorized', $post['category_slug']);
        self::assertSame('Administrador', $post['author']);
        self::assertNull($post['yoast_head_json']);
    }

    /**
     * Reuses successful reads for five hours before fetching fresh content.
     */
    #[Test]
    public function expiresTheApiCacheAfterFiveHours(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([$this->postPayload()])]);

        $this->news()->request('posts');
        $this->travel(17999)->seconds();
        $this->news()->request('posts');
        Http::assertSentCount(1);

        $this->travel(2)->seconds();
        $this->news()->request('posts');
        Http::assertSentCount(2);
    }

    /**
     * Ignores cache-busters and associative parameter order while separating filters.
     */
    #[Test]
    public function canonicalizesKeysWithoutMixingQueriesOrSites(): void
    {
        Http::fake(['*' => Http::response([$this->postPayload()])]);

        $this->news()->request('posts', ['per_page' => 1, '_embed' => 1, '_' => 1]);
        $this->news()->request('posts', ['_' => 99, '_embed' => 1, 'per_page' => 1]);
        Http::assertSentCount(1);

        $this->news()->request('posts', ['per_page' => 2, '_embed' => 1]);
        config()->set('news.wordpress.base_url', 'https://another-wordpress.test');
        $this->news()->request('posts', ['per_page' => 1, '_embed' => 1]);
        Http::assertSentCount(3);
    }

    /**
     * Disables cache reads and writes when the configured TTL is not positive.
     */
    #[Test]
    public function bypassesTheCacheWhenDisabled(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);

        foreach ([0, -1] as $ttl) {
            config()->set('news.cache.ttl', $ttl);
            $this->news()->request('posts');
            $this->news()->request('posts');
        }

        Http::assertSentCount(4);
    }

    /**
     * Caches empty successful collections and returns null for a missing slug.
     */
    #[Test]
    public function treatsEmptyCollectionsAsSuccessfulResults(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);

        self::assertNull($this->news()->getCategoryBySlug('missing'));
        self::assertNull($this->news()->getCategoryBySlug('missing'));
        Http::assertSentCount(1);
    }

    /**
     * Preserves named concurrent responses and reuses the complete successful batch.
     */
    #[Test]
    public function retrievesAndCachesNamedConcurrentCollections(): void
    {
        Http::fake([
            'wordpress.test/wp-json/wp/v2/categories*' => Http::response([['id' => 3, 'slug' => 'deportes', 'name' => 'Deportes']]),
            'wordpress.test/wp-json/wp/v2/posts*' => Http::response([$this->postPayload()]),
        ]);

        $result = $this->news()->getPostAndCategoryConcurrently('deportes', 'futbol');
        self::assertNotNull($result['category']);
        self::assertNotNull($result['post']);
        self::assertArrayHasKey('id', $result['category']);
        self::assertArrayHasKey('id', $result['post']);
        self::assertSame(3, $result['category']['id']);
        self::assertSame(7, $result['post']['id']);
        self::assertSame($result, $this->news()->getPostAndCategoryConcurrently('deportes', 'futbol'));
        Http::assertSentCount(2);
    }

    /**
     * Excludes uncategorized entries without dropping populated sports categories.
     */
    #[Test]
    public function filtersNavigationCategories(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([
            ['id' => 1, 'slug' => 'uncategorized'],
            ['id' => 2, 'slug' => 'sin-categoria'],
            ['id' => 3, 'slug' => 'deportes'],
        ])]);

        self::assertSame([['id' => 3, 'slug' => 'deportes']], $this->news()->getCategories());
    }

    /**
     * Reuses single reads inside batches and batch reads in subsequent single requests.
     */
    #[Test]
    public function sharesCachedResponsesAcrossSingleAndOverlappingBatches(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);

        $this->news()->request('categories', ['slug' => 'deportes']);
        $first = $this->news()->request([
            'category' => ['categories', ['slug' => 'deportes']],
            'posts' => ['posts', ['per_page' => 2]],
        ]);
        self::assertSame(['category' => [], 'posts' => []], $first);
        Http::assertSentCount(2);

        self::assertSame([], $this->news()->request('posts', ['per_page' => 2]));
        self::assertSame(['renamed' => [], 'other' => []], $this->news()->request([
            'renamed' => ['posts', ['per_page' => 2]],
            'other' => ['categories', ['slug' => 'otra']],
        ]));
        Http::assertSentCount(3);
    }

    /**
     * Fetches identical aliases once per batch even when persistent caching is disabled.
     */
    #[Test]
    public function deduplicatesAliasesWithoutChangingResultOrder(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);
        config()->set('news.cache.ttl', 0);

        $batch = [
            'first' => ['posts', ['per_page' => 2, '_embed' => 1]],
            'second' => ['posts', ['_embed' => 1, 'per_page' => 2]],
        ];
        self::assertSame(['first' => [], 'second' => []], $this->news()->request($batch));
        Http::assertSentCount(1);
        $this->news()->request($batch);
        Http::assertSentCount(2);
    }

    /**
     * Keeps each entry's original expiry when it is reused by a later batch.
     */
    #[Test]
    public function doesNotExtendCachedEntryLifetimeOnBatchHits(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);
        $this->news()->request('categories');
        $this->travel(17999)->seconds();
        $this->news()->request(['category' => ['categories'], 'post' => ['posts']]);
        Http::assertSentCount(2);

        $this->travel(2)->seconds();
        $this->news()->request(['post' => ['posts'], 'category' => ['categories']]);
        Http::assertSentCount(3);
    }

    /**
     * Preserves cached hits when a missing batch fails without caching partial results.
     */
    #[Test]
    public function preservesExistingHitsWhenMissingResponsesFail(): void
    {
        Http::fake([
            'wordpress.test/wp-json/wp/v2/categories*' => Http::response([]),
            'wordpress.test/wp-json/wp/v2/posts?slug=broken' => Http::response(['code' => 'failure'], 500),
            'wordpress.test/wp-json/wp/v2/posts*' => Http::response([]),
        ]);
        $this->news()->request('categories');

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $this->news()->request(['category' => ['categories'], 'post' => ['posts'], 'broken' => ['posts', ['slug' => 'broken']]]);
                self::fail('Expected an upstream error.');
            } catch (WordPressApiException) {
                self::assertSame([], $this->news()->request('categories'));
            }
        }

        Http::assertSentCount(5);
    }

    /**
     * Rejects upstream errors and malformed collections without caching the failure.
     */
    #[Test]
    #[DataProvider('invalidResponses')]
    public function rejectsInvalidResponsesWithoutCaching(string $body, int $status): void
    {
        Http::fake(['wordpress.test/*' => Http::sequence()->push($body, $status)->push([$this->postPayload()])]);

        try {
            $this->news()->request('posts');
            self::fail('The invalid response must be rejected.');
        } catch (WordPressApiException $exception) {
            self::assertSame($status, $exception->upstreamStatus);
            self::assertStringNotContainsString('private-upstream-detail', $exception->getMessage());
        }

        self::assertSame(7, $this->news()->request('posts')[0]['id']);
        Http::assertSentCount(2);
    }

    /**
     * Provides HTTP failures and JSON shapes that must never enter the cache.
     *
     * @return array<string, array{string, int}>
     */
    public static function invalidResponses(): array
    {
        return [
            'ip-restriction' => ['private-upstream-detail', 400],
            'unauthorized' => ['private-upstream-detail', 401],
            'forbidden' => ['private-upstream-detail', 403],
            'rate-limit' => ['private-upstream-detail', 429],
            'server-error' => ['private-upstream-detail', 503],
            'redirect' => ['', 302],
            'invalid-json' => ['not-json', 200],
            'null' => ['null', 200],
            'object' => ['{}', 200],
            'error-object' => ['{"code":"private-upstream-detail"}', 200],
            'invalid-row' => ['["invalid"]', 200],
            'missing-id' => ['[{"slug":"test"}]', 200],
            'missing-slug' => ['[{"id":1}]', 200],
        ];
    }

    /**
     * Retries only transient failures rather than IP or authorization errors.
     */
    #[Test]
    public function retriesTransientGetErrors(): void
    {
        config()->set('news.wordpress.retries', 1);
        Http::fake(['wordpress.test/*' => Http::sequence()->push([], 503)->push([$this->postPayload()])]);

        self::assertSame(7, $this->news()->request('posts')[0]['id']);
        Http::assertSentCount(2);
    }

    /**
     * Maps connection failures to safe errors without populating the response cache.
     */
    #[Test]
    public function handlesConnectionFailures(): void
    {
        Http::fake(['wordpress.test/*' => Http::failedConnection('private-upstream-detail')]);

        $this->expectException(WordPressApiException::class);
        $this->expectExceptionMessage('No se pudo consultar la API de noticias.');
        $this->news()->request('posts');
    }

    /**
     * Invalidates only one WordPress site's cache without flushing unrelated data.
     */
    #[Test]
    public function invalidatesOnlyNewsApiResponses(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([$this->postPayload()])]);
        Cache::store('array')->put('admin:unrelated', 'preserved', 36000);

        $this->news()->request('posts');
        $this->news()->invalidateCache();
        $this->news()->request('posts');

        self::assertSame('preserved', Cache::store('array')->get('admin:unrelated'));
        Http::assertSentCount(2);
    }

    /**
     * Supports the default untagged file store without deleting unrelated cached values.
     */
    #[Test]
    public function supportsTheUntaggedFileCacheStore(): void
    {
        $directory = storage_path('framework/testing/news-cache-'.Str::uuid());
        config()->set('cache.stores.news_test', ['driver' => 'file', 'path' => $directory]);
        config()->set('news.cache.store', 'news_test');
        Http::fake(['wordpress.test/*' => Http::response([$this->postPayload()])]);

        try {
            Cache::store('news_test')->put('unrelated', 'preserved', 36000);
            $this->news()->request('posts');
            $this->news()->request('posts');
            Http::assertSentCount(1);

            $this->news()->invalidateCache();
            $this->news()->request('posts');
            Http::assertSentCount(2);
            self::assertSame('preserved', Cache::store('news_test')->get('unrelated'));
        } finally {
            (new Filesystem)->deleteDirectory($directory);
        }
    }

    /**
     * Does not cache a partially successful concurrent batch.
     */
    #[Test]
    public function rejectsFailedConcurrentCollectionsWithoutCaching(): void
    {
        Http::fake([
            'wordpress.test/wp-json/wp/v2/categories*' => Http::sequence()->push([], 503)->push([]),
            'wordpress.test/wp-json/wp/v2/posts*' => Http::response([$this->postPayload()]),
        ]);

        try {
            $this->news()->getPostAndCategoryConcurrently('deportes', 'futbol');
            self::fail('A partial batch must not be cached.');
        } catch (WordPressApiException) {
            $result = $this->news()->getPostAndCategoryConcurrently('deportes', 'futbol');
            self::assertNull($result['category']);
            self::assertNotNull($result['post']);
            self::assertArrayHasKey('id', $result['post']);
            self::assertSame(7, $result['post']['id']);
        }

        Http::assertSentCount(4);
    }

    /**
     * Avoids retrying IP restrictions even when transient retries are enabled.
     */
    #[Test]
    public function doesNotRetryIpRestrictionErrors(): void
    {
        config()->set('news.wordpress.retries', 1);
        Http::fake(['wordpress.test/*' => Http::response([], 400)]);

        try {
            $this->news()->request('posts');
            self::fail('The restricted request must fail.');
        } catch (WordPressApiException $exception) {
            self::assertSame(400, $exception->upstreamStatus);
        }

        Http::assertSentCount(1);
    }

    /**
     * Applies transient retry handling to named concurrent requests as well.
     */
    #[Test]
    public function retriesTransientErrorsInsideAPool(): void
    {
        config()->set('news.wordpress.retries', 1);
        Http::fake([
            'wordpress.test/wp-json/wp/v2/categories*' => Http::response([]),
            'wordpress.test/wp-json/wp/v2/posts*' => Http::sequence()->push([], 503)->push([$this->postPayload()]),
        ]);

        $result = $this->news()->getPostAndCategoryConcurrently('deportes', 'futbol');

        self::assertNotNull($result['post']);
        self::assertArrayHasKey('id', $result['post']);
        self::assertSame(7, $result['post']['id']);
        Http::assertSentCount(3);
    }

    /**
     * Rejects unsafe origin configuration before touching HTTP or the response cache.
     */
    #[Test]
    #[DataProvider('invalidOrigins')]
    public function rejectsInvalidOrigins(string $baseUrl): void
    {
        config()->set('news.wordpress.base_url', $baseUrl);

        try {
            $this->news()->request('posts');
            self::fail('The origin must be validated before sending a request.');
        } catch (WordPressApiException) {
            Http::assertNothingSent();
        }
    }

    /**
     * Provides origins containing unsupported schemes or hidden request credentials.
     *
     * @return array<string, array{string}>
     */
    public static function invalidOrigins(): array
    {
        return [
            'missing-host' => ['https://'],
            'unsupported-scheme' => ['file:///etc/passwd'],
            'credentials' => ['https://user:password@wordpress.test'],
            'query-string' => ['https://wordpress.test?token=not-a-real-token'],
            'fragment' => ['https://wordpress.test#fragment'],
        ];
    }

    /**
     * Keeps an in-flight response in its old namespace when invalidation occurs.
     */
    #[Test]
    public function doesNotRepopulateAnInvalidatedNamespace(): void
    {
        Http::fake(function (): PromiseInterface {
            $this->news()->invalidateCache();

            return Http::response([$this->postPayload()]);
        });

        $this->news()->request('posts');
        $this->news()->request('posts');
        Http::assertSentCount(2);
    }

    /**
     * Prevents the read cache from being used as an arbitrary URL proxy.
     */
    #[Test]
    public function rejectsNonApiEndpointsBeforeSendingRequests(): void
    {
        try {
            $this->news()->request('https://elsewhere.test/page');
            self::fail('Only supported API endpoints may be requested.');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    /**
     * Prevents HTTP activity when the package is disabled.
     */
    #[Test]
    public function rejectsRequestsWhenTheModuleIsDisabled(): void
    {
        config()->set('news.enabled', false);

        try {
            $this->news()->request('posts');
            self::fail('Disabled packages must not send requests.');
        } catch (WordPressApiException) {
            Http::assertNothingSent();
        }
    }

    /**
     * Resolves the integration through its bound contract.
     */
    private function news(): NewsRepository
    {
        return $this->app->make(NewsRepository::class);
    }

    /**
     * Provides a public editorial payload without credentials or personal data.
     *
     * @return array<string, mixed>
     */
    private function postPayload(): array
    {
        return [
            'id' => 7, 'slug' => 'futbol', 'date' => '2026-11-24T10:00:00',
            'title' => ['rendered' => 'Fútbol &amp; noticias'],
            'content' => ['rendered' => '<p>Contenido editorial</p>'],
            'excerpt' => ['rendered' => '<p>Resumen</p>'],
            'categories' => [3], 'yoast_head_json' => ['title' => 'SEO'],
            'is_featured' => true, 'views_count' => 25,
            '_embedded' => [
                'author' => [['name' => 'Redacción']],
                'wp:featuredmedia' => [[
                    'source_url' => 'https://media.test/full.webp',
                    'media_details' => ['sizes' => ['rectangle-md' => ['source_url' => 'https://media.test/small.webp']]],
                ]],
                'wp:term' => [[['taxonomy' => 'category', 'slug' => 'deportes', 'name' => 'Deportes']]],
            ],
        ];
    }
}
