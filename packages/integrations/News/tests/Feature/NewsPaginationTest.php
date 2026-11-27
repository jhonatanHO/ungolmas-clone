<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Feature;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Verifies classic News pagination, canonical URLs, cache reuse, and failure states.
 */
final class NewsPaginationTest extends TestCase
{
    /**
     * Uses isolated API fixtures and avoids frontend build dependencies.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
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
     * Keeps homepage highlights unchanged while advancing only the forty-item listing.
     */
    #[Test]
    public function paginatesTheHomepageWithoutChangingHighlights(): void
    {
        $this->fakeArchive();
        $first = $this->get('/noticias')->assertOk()
            ->assertSee('aria-label="Paginación de noticias"', false)
            ->assertSee('href="'.route('news.index', ['page' => 2]).'"', false);
        $second = $this->get('/noticias?page=2')->assertOk()
            ->assertSee('rel="canonical" href="'.route('news.index', ['page' => 2]).'"', false);
        self::assertMatchesRegularExpression('/Página\s+2\s+de\s+3/', strip_tags($second->getContent()));

        self::assertCount(40, $first->viewData('posts'));
        self::assertCount(40, $second->viewData('posts'));
        self::assertSame([], array_intersect(array_column($first->viewData('posts'), 'id'), array_column($second->viewData('posts'), 'id')));
        self::assertSame($first->viewData('hero'), $second->viewData('hero'));
        self::assertSame($first->viewData('featured'), $second->viewData('featured'));
        self::assertSame(81, $second->viewData('pagination')->total());

        Http::assertSent(static fn (Request $request): bool => ($request['page'] ?? null) === 2 && ($request['per_page'] ?? null) === 40);
    }

    /**
     * Preserves category filters and excludes unrelated query parameters from pagination URLs.
     */
    #[Test]
    public function paginatesCategoriesWithTheirOwnCanonicalUrls(): void
    {
        $this->fakeArchive();
        $response = $this->get('/noticias/deportes?page=2&unused=discard')->assertOk()
            ->assertSee('rel="canonical" href="'.route('news.category', ['category_slug' => 'deportes', 'page' => 2]).'"', false)
            ->assertSee('href="'.route('news.category', ['category_slug' => 'deportes', 'page' => 3]).'"', false)
            ->assertDontSee('unused=discard');

        self::assertCount(24, $response->viewData('posts'));
        self::assertSame(49, $response->viewData('pagination')->total());
        Http::assertSent(static fn (Request $request): bool => ($request['categories'] ?? null) === 3 && ($request['page'] ?? null) === 2 && ($request['per_page'] ?? null) === 24);
    }

    /**
     * Stops navigation at the final page and preserves first-page canonical URLs.
     */
    #[Test]
    public function handlesFirstAndLastPagesWithoutNonexistentNextLinks(): void
    {
        $this->fakeArchive();
        $first = $this->get('/noticias?page=1')->assertOk()
            ->assertSee('rel="canonical" href="'.route('news.index').'"', false)
            ->assertDontSee('rel="prev"', false);
        self::assertSame(1, $first->viewData('pagination')->currentPage());

        foreach (['/noticias?page=3', '/noticias/deportes?page=3'] as $url) {
            $last = $this->get($url)->assertOk()->assertDontSee('rel="next"', false);
            self::assertMatchesRegularExpression('/Página\s+3\s+de\s+3/', strip_tags($last->getContent()));
            self::assertCount(1, $last->viewData('posts'));
        }
    }

    /**
     * Reuses homepage collections and page metadata without refetching cached pages.
     */
    #[Test]
    public function reusesCachedPagesAndUnchangedHomepageCollections(): void
    {
        $this->fakeArchive();
        foreach (['/noticias', '/noticias?page=2', '/noticias?page=1', '/noticias?page=2'] as $url) {
            $this->get($url)->assertOk();
        }

        // NOTE: One navigation, one hero, one featured collection, and two listing pages.
        Http::assertSentCount(5);
    }

    /**
     * Returns a genuine 404 for WordPress's explicit nonexistent-page response.
     */
    #[Test]
    public function returns404ForPagesBeyondTheArchive(): void
    {
        $this->fakeArchive();
        $this->get('/noticias?page=4')->assertNotFound();
        $this->get('/noticias/deportes?page=4')->assertNotFound();
    }

    /**
     * Rejects invalid query values without querying the article collections.
     */
    #[Test]
    #[DataProvider('invalidPageQueries')]
    public function rejectsMalformedPageNumbers(string $query): void
    {
        $this->fakeArchive();
        $this->get('/noticias?'.$query)->assertNotFound();
        $this->get('/noticias/deportes?'.$query)->assertNotFound();
        Http::assertNotSent(static fn (Request $request): bool => str_contains($request->url(), '/posts'));
    }

    /**
     * Supplies noncanonical, nonpositive, overflowing, and array-shaped page values.
     *
     * @return array<string, array{string}>
     */
    public static function invalidPageQueries(): array
    {
        return [
            'zero' => ['page=0'], 'negative' => ['page=-1'], 'decimal' => ['page=1.5'],
            'text' => ['page=abc'], 'array' => ['page[]=2'], 'empty' => ['page='],
            'overflow' => ['page=9999999999999999999999999999'], 'leading-zero' => ['page=02'],
        ];
    }

    /**
     * Keeps an upstream failure distinct from an invalid page and retries the same category page.
     */
    #[Test]
    public function retriesTheSamePageAfterAnUpstreamFailure(): void
    {
        Http::fake([
            'wordpress.test/wp-json/wp/v2/categories*' => Http::response([['id' => 3, 'slug' => 'deportes', 'name' => 'Deportes']]),
            'wordpress.test/wp-json/wp/v2/posts*' => Http::response(['code' => 'upstream_failure'], 503),
        ]);

        $this->get('/noticias/deportes?page=2')->assertStatus(502)
            ->assertSee('href="'.route('news.category', ['category_slug' => 'deportes', 'page' => 2]).'"', false)
            ->assertSee('Volver');
    }

    /**
     * Shows empty first pages without generating links to nonexistent pages.
     */
    #[Test]
    public function rendersEmptyArchivesWithoutPaginationControls(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([], 200, ['X-WP-Total' => '0', 'X-WP-TotalPages' => '0'])]);
        $this->get('/noticias')->assertOk()->assertDontSee('aria-label="Paginación de noticias"', false);
    }

    /**
     * Reserves the primary color for the selected page and keeps navigation controls neutral.
     */
    #[Test]
    public function highlightsOnlyTheSelectedPageWithThePrimaryColor(): void
    {
        $this->fakeArchive();
        $response = $this->get('/noticias?page=2')->assertOk();
        self::assertSame(1, preg_match('~<nav aria-label="Paginación de noticias".*?</nav>~s', $response->getContent(), $matches));
        self::assertStringNotContainsString('news-headline', $matches[0]);
        self::assertStringContainsString('aria-current="page"', $matches[0]);
        self::assertStringContainsString('rounded-full', $matches[0]);
        self::assertStringContainsString('focus-visible:outline-white', $matches[0]);
        self::assertSame(1, preg_match('~<span aria-current="page"[^>]*class="([^"]+)"~s', $matches[0], $active));
        self::assertStringContainsString('bg-news-accent', $active[1]);
        preg_match_all('~<a\b[^>]*class="([^"]+)"~s', $matches[0], $links);
        foreach ($links[1] as $classes) {
            self::assertStringNotContainsString('news-accent', $classes);
        }
    }

    /**
     * Limits both mobile archives while preserving their independent desktop page sizes.
     *
     * @param  array<string, string>  $headers
     */
    #[Test]
    #[DataProvider('homepageDevices')]
    public function limitsOnlyMobileListings(array $headers, int $perPage): void
    {
        $this->fakeArchive();
        $response = $this->withHeaders($headers)->get('/noticias')->assertOk();
        self::assertCount($perPage, $response->viewData('posts'));
        self::assertSame($perPage, $response->viewData('pagination')->perPage());
        self::assertSame((int) ceil(81 / $perPage), $response->viewData('pagination')->lastPage());
        $vary = implode(', ', $response->headers->all('Vary'));
        self::assertStringContainsString('User-Agent', $vary);
        self::assertStringContainsString('Sec-CH-UA-Mobile', $vary);

        $category = $this->get('/noticias/deportes')->assertOk();
        $categorySize = $perPage === 12 ? 12 : 24;
        self::assertCount($categorySize, $category->viewData('posts'));
        self::assertSame($categorySize, $category->viewData('pagination')->perPage());
        self::assertStringContainsString('Sec-CH-UA-Mobile', implode(', ', $category->headers->all('Vary')));
    }

    /**
     * Provides smartphone browsers and explicit desktop/mobile client hints.
     *
     * @return array<string, array{array<string, string>, int}>
     */
    public static function homepageDevices(): array
    {
        return [
            'iphone' => [['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Mobile Safari/604.1'], 12],
            'android' => [['User-Agent' => 'Mozilla/5.0 (Linux; Android 15) Chrome/130.0 Mobile Safari/537.36'], 12],
            'desktop' => [['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0 Safari/537.36'], 40],
            'mobile-hint' => [['User-Agent' => 'Mozilla/5.0', 'Sec-CH-UA-Mobile' => '?1'], 12],
            'desktop-hint' => [['User-Agent' => 'Mozilla/5.0 Mobile', 'Sec-CH-UA-Mobile' => '?0'], 40],
        ];
    }

    /**
     * Keeps mobile and desktop page bodies and totals separate without duplicating highlights.
     */
    #[Test]
    public function separatesMobileAndDesktopHomepageCaches(): void
    {
        $this->fakeArchive();
        $desktop = $this->withHeaders(['Sec-CH-UA-Mobile' => '?0'])->get('/noticias')->assertOk();
        $mobile = $this->withHeaders(['Sec-CH-UA-Mobile' => '?1'])->get('/noticias')->assertOk();
        $mobileNext = $this->get('/noticias?page=2')->assertOk();
        $desktopNext = $this->withHeaders(['Sec-CH-UA-Mobile' => '?0'])->get('/noticias?page=2')->assertOk();
        $this->withHeaders(['Sec-CH-UA-Mobile' => '?1'])->get('/noticias?page=2')->assertOk();

        self::assertCount(40, $desktop->viewData('posts'));
        self::assertCount(12, $mobile->viewData('posts'));
        self::assertCount(40, $desktopNext->viewData('posts'));
        self::assertCount(12, $mobileNext->viewData('posts'));
        self::assertSame([], array_intersect(array_column($mobile->viewData('posts'), 'id'), array_column($mobileNext->viewData('posts'), 'id')));
        self::assertSame($desktop->viewData('hero'), $mobile->viewData('hero'));
        self::assertSame($desktop->viewData('featured'), $mobile->viewData('featured'));
        Http::assertSentCount(7);
    }

    /**
     * Calculates mobile last-page links using the twelve-item page size.
     */
    #[Test]
    public function handlesTheMobileHomepageLastPage(): void
    {
        $this->fakeArchive();
        $last = $this->withHeaders(['Sec-CH-UA-Mobile' => '?1'])->get('/noticias?page=7')
            ->assertOk()->assertDontSee('rel="next"', false);
        self::assertCount(9, $last->viewData('posts'));
        self::assertSame(7, $last->viewData('pagination')->lastPage());
        $this->get('/noticias?page=8')->assertNotFound();
    }

    /**
     * Separates category cache variants and stops at the mobile archive's last page.
     */
    #[Test]
    public function separatesMobileCategoryPagesFromDesktopPages(): void
    {
        $this->fakeArchive();
        $desktop = $this->withHeaders(['Sec-CH-UA-Mobile' => '?0'])->get('/noticias/deportes?page=2')->assertOk();
        $mobile = $this->withHeaders(['Sec-CH-UA-Mobile' => '?1'])->get('/noticias/deportes?page=2')->assertOk();
        self::assertCount(24, $desktop->viewData('posts'));
        self::assertCount(12, $mobile->viewData('posts'));
        self::assertSame(3, $desktop->viewData('pagination')->lastPage());
        self::assertSame(5, $mobile->viewData('pagination')->lastPage());
        $this->get('/noticias/deportes?page=2')->assertOk();
        Http::assertSentCount(4);

        $last = $this->get('/noticias/deportes?page=5')->assertOk()->assertDontSee('rel="next"', false);
        self::assertCount(1, $last->viewData('posts'));
        $this->get('/noticias/deportes?page=6')->assertNotFound();
        Http::assertSentCount(6);
    }

    /**
     * Simulates stable article archives and WordPress pagination metadata.
     */
    private function fakeArchive(): void
    {
        Http::fake(static function (Request $request): PromiseInterface {
            if (str_contains($request->url(), '/categories')) {
                return Http::response([['id' => 3, 'slug' => 'deportes', 'name' => 'Deportes']]);
            }

            $page = (int) ($request['page'] ?? 1);
            $perPage = (int) ($request['per_page'] ?? 40);
            $featured = ($request['featured'] ?? null) === 'true';
            $total = $featured ? 4 : (($request['categories'] ?? null) === 3 ? 49 : 81);
            $lastPage = (int) ceil($total / $perPage);
            if ($page > $lastPage) {
                return Http::response(['code' => 'rest_post_invalid_page_number'], 400);
            }

            $offset = ($page - 1) * $perPage;
            $count = min($perPage, $total - $offset);
            $posts = [];
            for ($index = 0; $index < $count; $index++) {
                $id = ($featured ? 9000 : 1000) + $offset + $index;
                $posts[] = [
                    'id' => $id, 'slug' => 'noticia-'.$id, 'categories' => [3],
                    'title' => ['rendered' => 'Noticia '.$id],
                    '_embedded' => ['wp:term' => [[['taxonomy' => 'category', 'slug' => 'deportes', 'name' => 'Deportes']]]],
                ];
            }

            return Http::response($posts, 200, ['X-WP-Total' => (string) $total, 'X-WP-TotalPages' => (string) $lastPage]);
        });
    }
}
