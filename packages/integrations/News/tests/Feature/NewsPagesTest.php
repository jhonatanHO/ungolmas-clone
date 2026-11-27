<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Feature;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Ugm\News\Contracts\NewsRepository;

/**
 * Verifies public News navigation, safe content, and reusable Blade components offline.
 */
final class NewsPagesTest extends TestCase
{
    /**
     * Isolates API responses and skips compiled frontend assets in HTTP tests.
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
        ]);
        Http::preventStrayRequests();
    }

    /**
     * Displays the source homepage structure and links to the canonical article route.
     */
    #[Test]
    public function showsTheHomepageWithReusableBladeComponents(): void
    {
        $this->fakeContent();
        $this->get('/noticias')->assertOk()
            ->assertViewIs('news::index')
            ->assertSee('Noticias de hoy')
            ->assertSee('Últimas noticias')
            ->assertSee('Partido destacado')
            ->assertSee('/noticia/deportes/partido', false)
            ->assertSee('loading="eager"', false)
            ->assertDontSee('data-src=', false)
            ->assertDontSee('v-pre', false);
    }

    /**
     * Keeps header navigation limited to the explicitly requested News destination.
     */
    #[Test]
    public function exposesOnlyTheRequestedNewsHeaderLink(): void
    {
        $this->fakeContent();

        $response = $this->get('/noticias')->assertOk()
            ->assertDontSee('/webapp', false)
            ->assertDontSee('fonts.googleapis.com', false);

        self::assertSame(1, preg_match('~<header\b[^>]*>(.*?)</header>~s', $response->getContent(), $matches));
        self::assertSame(1, preg_match_all('~<a\b~', $matches[1]));
        self::assertSame('Noticias', trim(strip_tags($matches[1])));
        self::assertStringContainsString(route('news.index'), $matches[1]);
        self::assertStringContainsString('role="img" aria-label="UNGOLMAS"', $matches[1]);
        self::assertStringContainsString(File::get(base_path('packages/integrations/News/resources/assets/images/ugm-logo.svg')), $matches[1]);
        self::assertStringNotContainsString('<img', $matches[1]);
    }

    /**
     * Restores the original official channels in every public News page footer.
     */
    #[Test]
    public function showsTheOriginalOfficialChannelsInTheFooter(): void
    {
        $this->fakeContent();

        foreach (['/noticias', '/noticias/deportes', '/noticia/deportes/partido'] as $url) {
            $response = $this->get($url)->assertOk();
            self::assertSame(1, preg_match('~<footer\b[^>]*>(.*?)</footer>~s', $response->getContent(), $matches));
            self::assertStringContainsString('Síguenos en nuestros canales oficiales', $matches[1]);
            self::assertStringContainsString('Powered by', $matches[1]);
            self::assertStringContainsString('alt="LNM &amp; Combativa"', $matches[1]);
            self::assertSame(3, preg_match_all('~<a\b~', $matches[1]));
            foreach ([
                'https://www.facebook.com/profile.php?id=61562935859606',
                'https://whatsapp.com/channel/0029VbCtj5cKGGGIWYszWC2r',
                'https://www.instagram.com/ungolmas.oficial/',
            ] as $channel) {
                self::assertStringContainsString('href="'.$channel.'"', $matches[1]);
            }
            self::assertSame(3, substr_count($matches[1], 'rel="noopener noreferrer"'));
        }
    }

    /**
     * Keeps official channel components independent from article-sharing interactions.
     */
    #[Test]
    public function rendersRepeatedOfficialChannelComponentsWithoutScripts(): void
    {
        $html = Blade::render('<x-news::official-channels /><x-news::official-channels />');

        self::assertSame(6, preg_match_all('~<a\b~', $html));
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString(' id=', $html);
        self::assertStringNotContainsString('sharer.php', $html);
        self::assertStringNotContainsString('data-news-copy', $html);
    }

    /**
     * Keeps literal palette values out of Blade while retaining browser theme metadata.
     */
    #[Test]
    public function usesPaletteTokensInsteadOfHexColorsInTemplates(): void
    {
        foreach (File::allFiles(base_path('packages/integrations/News/resources/views')) as $file) {
            if (str_ends_with($file->getFilename(), '.blade.php')) {
                self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $file->getContents(), $file->getRelativePathname());
            }
        }

        $this->fakeContent();
        $this->get('/noticias')->assertOk()
            ->assertSee('name="theme-color" content="#000000"', false)
            ->assertSee('bg-news-page', false)
            ->assertSee('bg-news-border', false);
    }

    /**
     * Renders sparse featured results without indexing missing positions.
     */
    #[Test]
    #[DataProvider('featuredCounts')]
    public function rendersSparseFeaturedCollections(int $count): void
    {
        $this->fakeContent($count);

        $this->get('/noticias')->assertOk()->assertSee('Últimas noticias');
    }

    /**
     * Provides featured collection sizes around the source template's unsafe indexes.
     *
     * @return array<string, array{int}>
     */
    public static function featuredCounts(): array
    {
        return ['none' => [0], 'one' => [1], 'two' => [2], 'three' => [3], 'four' => [4]];
    }

    /**
     * Shows navigation and readable empty states when no posts are published.
     */
    #[Test]
    public function rendersAnEmptyHomepage(): void
    {
        Http::fake(['wordpress.test/*' => Http::response([], 200, ['X-WP-Total' => '0', 'X-WP-TotalPages' => '0'])]);

        $this->get('/noticias')->assertOk()->assertSee('noticias publicadas por el momento.');
    }

    /**
     * Displays category articles and marks the active category in navigation.
     */
    #[Test]
    public function showsACategoryPage(): void
    {
        $this->fakeContent();
        $this->get('/noticias/deportes')->assertOk()
            ->assertViewIs('news::categories')
            ->assertSee('Deportes')
            ->assertSee('aria-current="page"', false)
            ->assertSee('Partido destacado');

        Http::assertSent(static fn (Request $request): bool => str_contains($request->url(), '/posts?') && $request['categories'] === 3);
    }

    /**
     * Renders sanitized article content, canonical metadata, responsive images, and share links.
     */
    #[Test]
    public function showsTheSingularArticleRouteSafely(): void
    {
        $this->fakeContent();
        $this->get('/noticia/deportes/partido')->assertOk()
            ->assertViewIs('news::single')
            ->assertSee('<h2>Crónica del partido</h2>', false)
            ->assertSee('https://media.test/article.webp', false)
            ->assertSee('id="populares"', false)
            ->assertSee('data-news-share', false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('rel="canonical" href="'.route('news.post', ['category_slug' => 'deportes', 'slug' => 'partido']).'"', false)
            ->assertDontSee('window.evil', false)
            ->assertDontSee('onclick="', false)
            ->assertDontSee('onerror="', false)
            ->assertDontSee('javascript:', false);

        Http::assertNotSent(static fn (Request $request): bool => $request->method() !== 'GET');
    }

    /**
     * Preserves old article links through a permanent redirect without requesting content.
     */
    #[Test]
    public function redirectsTheSourcePluralArticleUrl(): void
    {
        $this->get('/noticias/deportes/partido')->assertStatus(301)->assertRedirect('/noticia/deportes/partido');
        Http::assertNothingSent();
    }

    /**
     * Returns 404 rather than an empty page when a category does not exist.
     */
    #[Test]
    public function returns404ForMissingCategories(): void
    {
        $this->fakeContent();
        $this->get('/noticias/missing')->assertNotFound();
    }

    /**
     * Returns 404 when the requested post does not exist.
     */
    #[Test]
    public function returns404ForMissingArticles(): void
    {
        $this->fakeContent();
        $this->get('/noticia/deportes/missing')->assertNotFound();
    }

    /**
     * Prevents a valid post from being shown under an unrelated category.
     */
    #[Test]
    public function validatesArticleCategoryMembership(): void
    {
        $this->fakeContent();
        $this->get('/noticia/otra/partido')->assertNotFound();
    }

    /**
     * Shows a safe 502 page instead of leaking the remote error body.
     */
    #[Test]
    public function rendersAnUpstreamFailurePage(): void
    {
        Http::fake(['wordpress.test/*' => Http::response('private-upstream-details', 400)]);

        $this->get('/noticias')->assertStatus(502)
            ->assertSee('Las noticias no están disponibles temporalmente')
            ->assertSee('noindex,follow', false)
            ->assertDontSee('private-upstream-details');
    }

    /**
     * Keeps a valid article readable when the optional popular-post collection fails.
     */
    #[Test]
    public function toleratesAnUnavailablePopularFeed(): void
    {
        $this->fakeContent(popularFails: true);

        $this->get('/noticia/deportes/partido')->assertOk()->assertSee('Crónica del partido')->assertDontSee('id="populares"', false);
    }

    /**
     * Prepares navigation once per page without repeating the repository lookup in controllers.
     */
    #[Test]
    public function loadsNavigationOnceForEachPublicNewsPage(): void
    {
        $categories = [['id' => 3, 'slug' => 'deportes', 'name' => 'Deportes']];
        $this->fakeContent();
        $repository = \Mockery::mock(NewsRepository::class);
        $repository->shouldReceive('getCategories')->times(3)->andReturn($categories);
        $repository->shouldReceive('getIndexPosts')->with(1, 40)->once()->andReturn(['total' => 0]);
        $repository->shouldReceive('getCategoryBySlug')->with('deportes')->once()->andReturn($categories[0]);
        $repository->shouldReceive('getCategoryPostsPage')->with(3, 1, 24)->once()->andReturn(['posts' => [], 'total' => 0, 'totalPages' => 0]);
        $repository->shouldReceive('getPostAndCategoryConcurrently')->with('deportes', 'partido')->once()->andReturn([
            'category' => $categories[0],
            'post' => ['id' => 7, 'slug' => 'partido', 'title' => 'Partido', 'categories' => [3]],
        ]);
        $repository->shouldReceive('request')->once()->andReturn([]);
        $this->app->instance(NewsRepository::class, $repository);

        foreach (['/noticias', '/noticias/deportes', '/noticia/deportes/partido'] as $url) {
            $this->get($url)->assertOk()->assertViewHas('wpCategories', $categories);
        }

        Http::assertNothingSent();
    }

    /**
     * Keeps published content available when only the navigation request fails.
     */
    #[Test]
    public function toleratesUnavailableNavigationCategories(): void
    {
        Http::fake([
            'wordpress.test/wp-json/wp/v2/categories*' => Http::response([], 503),
            'wordpress.test/wp-json/wp/v2/posts*' => Http::response([], 200, ['X-WP-Total' => '0', 'X-WP-TotalPages' => '0']),
        ]);

        $this->get('/noticias')->assertOk()->assertViewHas('wpCategories', []);
    }

    /**
     * Does not load or globally share News navigation for unrelated routes.
     */
    #[Test]
    public function keepsNavigationScopedToNewsRequests(): void
    {
        $this->fakeContent();
        $this->get('/noticias')->assertOk();
        self::assertArrayNotHasKey('wpCategories', app('view')->getShared());

        Route::get('/_test-outside-news', static fn (\Illuminate\Http\Request $request): array => [
            'has_news_categories' => $request->attributes->has('news.categories'),
        ]);
        Http::fake();
        $this->get('/_test-outside-news')->assertOk()->assertExactJson(['has_news_categories' => false]);
        Http::assertNothingSent();
    }

    /**
     * Allows navigation and share components to be rendered repeatedly without global IDs or functions.
     */
    #[Test]
    public function rendersIndependentComponentInstances(): void
    {
        $html = Blade::render('<x-news::news-nav-categories /><x-news::news-nav-categories /><x-news::share-page /><x-news::share-page />');

        self::assertSame(2, substr_count($html, 'data-news-share>'));
        self::assertStringNotContainsString('copyCurrentUrl', $html);
        self::assertStringNotContainsString('id="copy-toast"', $html);
    }

    /**
     * Restricts heading variants and provides an inline fallback for absent images.
     */
    #[Test]
    public function validatesCardVariantsAndImageFallbacks(): void
    {
        $html = Blade::render('<x-news::news-block :post="$post" as="script" variant="invalid" />', [
            'post' => ['id' => 1, 'slug' => 'partido', 'category_slug' => 'deportes', 'title' => '<script>escaped</script>'],
        ]);

        self::assertStringContainsString('<h3', $html);
        self::assertStringContainsString('Imagen no disponible', $html);
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('rounded-xl', $html);
        self::assertStringContainsString('aspect-[3/2]', $html);
        self::assertStringContainsString('font-news-copy', $html);
    }

    /**
     * Returns predictable public fixtures without contacting WordPress.
     */
    private function fakeContent(int $featuredCount = 1, bool $popularFails = false): void
    {
        Http::fake(function (Request $request) use ($featuredCount, $popularFails): PromiseInterface {
            if (str_contains($request->url(), '/categories')) {
                if (($request['slug'] ?? null) === 'missing') {
                    return Http::response([]);
                }

                return Http::response([['id' => ($request['slug'] ?? null) === 'otra' ? 9 : 3, 'slug' => $request['slug'] ?? 'deportes', 'name' => 'Deportes']]);
            }

            if (($request['slug'] ?? null) === 'missing') {
                return Http::response([]);
            }

            if ($popularFails && ($request['orderby'] ?? null) === 'views') {
                return Http::response([], 503);
            }

            $post = [
                'id' => 7, 'slug' => 'partido', 'date' => '2026-11-24T10:00:00',
                'title' => ['rendered' => 'Partido destacado'], 'categories' => [3],
                'excerpt' => ['rendered' => '<p>La actualidad del fútbol.</p>'],
                'content' => ['rendered' => '<h2>Crónica del partido</h2><p>Una gran jornada.</p><img src="https://media.test/article.webp" onerror="window.evil()"><script>window.evil()</script><a href="javascript:evil()" onclick="evil()">Enlace</a>'],
                '_embedded' => [
                    'wp:term' => [[['taxonomy' => 'category', 'slug' => 'deportes', 'name' => 'Deportes']]],
                    'wp:featuredmedia' => [['source_url' => 'https://media.test/post.webp']],
                ],
            ];

            if (($request['featured'] ?? null) === 'true') {
                return Http::response(array_map(static fn (int $index): array => array_replace($post, [
                    'id' => 20 + $index, 'slug' => 'destacada-'.$index,
                ]), $featuredCount === 0 ? [] : range(1, $featuredCount)));
            }

            return Http::response([$post], 200, ['X-WP-Total' => '1', 'X-WP-TotalPages' => '1']);
        });
    }
}
