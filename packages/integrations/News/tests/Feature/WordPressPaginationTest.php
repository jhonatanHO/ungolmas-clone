<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Feature;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Exceptions\WordPressApiException;

/**
 * Verifies that WordPress bodies and pagination totals share the same scoped cache.
 */
final class WordPressPaginationTest extends TestCase
{
    /**
     * Isolates cached API responses and prevents external requests.
     */
    protected function setUp(): void
    {
        parent::setUp();
        config()->set([
            'news.enabled' => true, 'news.wordpress.base_url' => 'https://wordpress.test',
            'news.wordpress.retries' => 0, 'news.cache.store' => 'array', 'news.cache.ttl' => 18000,
        ]);
        Http::preventStrayRequests();
    }

    /**
     * Shares cached bodies and totals between legacy reads and paginated category reads.
     */
    #[Test]
    public function sharesPaginationMetadataWithExistingApiReads(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([], 200, ['X-WP-Total' => '0', 'X-WP-TotalPages' => '0'])]);
        $news = app(NewsRepository::class);
        $news->getPostsByCategory(3);
        self::assertSame(['posts' => [], 'total' => 0, 'totalPages' => 0], $news->getCategoryPostsPage(3));
        Http::assertSentCount(1);

        $news->getCategoryPostsPage(9);
        Http::assertSentCount(2);
    }

    /**
     * Separates page and category keys and expires totals together with article bodies.
     */
    #[Test]
    public function cachesPagesAndTotalsForFiveHoursWithoutExtendingHits(): void
    {
        Http::fake(static fn (Request $request): PromiseInterface => Http::response(
            array_map(static fn (int $id): array => ['id' => $id, 'slug' => 'article-'.$id],
                (int) ($request['page'] ?? 1) === 1 ? range(1, 24) : [25]),
            200, ['X-WP-Total' => '25', 'X-WP-TotalPages' => '2'],
        ));
        $news = app(NewsRepository::class);
        $first = $news->getCategoryPostsPage(3, 2);
        self::assertSame(25, $first['total']);
        self::assertSame(2, $first['totalPages']);
        self::assertSame($first, $news->getCategoryPostsPage(3, 2));
        Http::assertSentCount(1);

        $news->getCategoryPostsPage(9, 2);
        $news->getCategoryPostsPage(3, 1);
        Http::assertSentCount(3);

        $this->travel(17999)->seconds();
        $news->getCategoryPostsPage(3, 2);
        Http::assertSentCount(3);
        $this->travel(2)->seconds();
        $news->getCategoryPostsPage(3, 2);
        Http::assertSentCount(4);
    }

    /**
     * Rejects absent or contradictory totals without caching the failed paginated response.
     *
     * @param  array<string, string>  $headers
     */
    #[Test]
    #[DataProvider('invalidTotals')]
    public function rejectsInvalidPaginationHeadersWithoutCaching(array $headers): void
    {
        Http::fake(['wordpress.test/*' => Http::response([], 200, $headers)]);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                app(NewsRepository::class)->getCategoryPostsPage(3);
                self::fail('Expected invalid pagination metadata to be rejected.');
            } catch (WordPressApiException $exception) {
                self::assertNull($exception->upstreamStatus);
            }
        }
        Http::assertSentCount(2);
    }

    /**
     * Does not cache partial homepage collections when listing metadata is unavailable.
     */
    #[Test]
    public function rejectsInvalidHomepageMetadataWithoutCachingTheBatch(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([])]);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                app(NewsRepository::class)->getIndexPosts();
                self::fail('Expected missing homepage pagination metadata to be rejected.');
            } catch (WordPressApiException $exception) {
                self::assertNull($exception->upstreamStatus);
            }
        }
        Http::assertSentCount(6);
    }

    /**
     * Supplies missing, malformed, negative, and contradictory metadata.
     *
     * @return array<string, array{array<string, string>}>
     */
    public static function invalidTotals(): array
    {
        return [
            'missing' => [[]],
            'partial' => [['X-WP-Total' => '25']],
            'text' => [['X-WP-Total' => 'invalid', 'X-WP-TotalPages' => '1']],
            'negative' => [['X-WP-Total' => '-1', 'X-WP-TotalPages' => '0']],
            'contradictory' => [['X-WP-Total' => '25', 'X-WP-TotalPages' => '1']],
            'incomplete-list' => [['X-WP-Total' => '1', 'X-WP-TotalPages' => '1']],
        ];
    }
}
