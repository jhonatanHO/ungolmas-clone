<?php

declare(strict_types=1);

namespace Ugm\News\Services;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Exceptions\WordPressApiException;

/**
 * Retrieves and normalizes WordPress collections through bounded API-only requests.
 */
final class WordPressService implements NewsRepository
{
    /**
     * Injects the package configuration, isolated cache, and safe logger.
     */
    public function __construct(
        private readonly Repository $config,
        private readonly NewsCacheService $cache,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Retrieves a validated collection or named concurrent batch through the API cache.
     *
     * @param  string|array<string, array{0: string, 1?: array<string, mixed>}>  $requests
     * @param  array<string, mixed>  $params
     * @return array<mixed>
     *
     * @throws WordPressApiException
     */
    public function request(string|array $requests, array $params = []): array
    {
        $batch = is_string($requests) ? ['result' => [$requests, $params]] : $requests;
        $collections = $this->requestCollections($batch);
        $results = array_map(static fn (array $collection): array => $collection['items'], $collections);

        return is_string($requests) ? $results['result'] : $results;
    }

    /**
     * Caches collection bodies and pagination headers together for all read consumers.
     *
     * @param  array<string, array{0: string, 1?: array<string, mixed>}>  $batch
     * @param  array<string, array{page: int, perPage: int}>  $pagination
     * @return array<string, array{items: list<array<string, mixed>>, total: ?int, totalPages: ?int}>
     */
    private function requestCollections(array $batch, array $pagination = []): array
    {
        $apiUrl = $this->apiUrl();

        foreach ($batch as $request) {
            if (! in_array($request[0], ['posts', 'categories'], true)) {
                throw new InvalidArgumentException('Only named WordPress posts and categories GET requests are supported.');
            }
        }

        $decode = function (Response $response, string $name) use ($batch, $pagination): array {
            $collection = $this->decode($response, $batch[$name][0]);
            if (isset($pagination[$name])) {
                $this->postsPage($collection, $pagination[$name]['page'], $pagination[$name]['perPage']);
            }

            return $collection;
        };

        $results = $this->cache->remember($apiUrl, $batch, function (array $missing) use ($apiUrl, $decode): array {
            try {
                if (count($missing) === 1) {
                    $name = array_key_first($missing);
                    $request = $missing[$name];

                    return [$name => $decode(
                        $this->http(Http::acceptJson())->get($apiUrl.'/'.$request[0], $request[1] ?? []),
                        $name,
                    )];
                }

                $responses = Http::pool(function (Pool $pool) use ($missing, $apiUrl): array {
                    $pending = [];
                    foreach ($missing as $name => $request) {
                        $pending[] = $this->http($pool->as($name))->get($apiUrl.'/'.$request[0], $request[1] ?? []);
                    }

                    return $pending;
                });

                $results = [];
                foreach ($missing as $name => $request) {
                    $response = $responses[$name] ?? null;
                    if (! $response instanceof Response) {
                        throw new WordPressApiException;
                    }
                    $results[$name] = $decode($response, $name);
                }

                return $results;
            } catch (ConnectionException) {
                $this->logger->warning('WordPress News API connection failed.');
                throw new WordPressApiException;
            }
        });

        $collections = [];
        foreach ($results as $name => $collection) {
            $items = $collection['items'] ?? null;
            $total = $collection['total'] ?? null;
            $totalPages = $collection['totalPages'] ?? null;
            if (! is_array($items) || ! array_is_list($items)
                || ($total !== null && ! is_int($total)) || ($totalPages !== null && ! is_int($totalPages))) {
                throw new WordPressApiException;
            }

            $rows = [];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    throw new WordPressApiException;
                }
                $row = [];
                foreach ($item as $key => $value) {
                    if (! is_string($key)) {
                        throw new WordPressApiException;
                    }
                    $row[$key] = $value;
                }
                $rows[] = $row;
            }
            $collections[$name] = ['items' => $rows, 'total' => $total, 'totalPages' => $totalPages];
        }

        return $collections;
    }

    /**
     * Retrieves the source homepage's hero, featured posts, and latest posts.
     *
     * @return array<mixed>
     */
    public function getIndexPosts(int $page = 1, int $perPage = 40): array
    {
        $this->validatePage($page, $perPage);
        $collections = $this->requestCollections([
            'hero' => ['posts', ['_embed' => 1, 'per_page' => 1]],
            'postsTop' => ['posts', ['_embed' => 1, 'per_page' => 4, 'featured' => 'true']],
            'posts' => ['posts', ['_embed' => 1, 'per_page' => $perPage, ...($page > 1 ? ['page' => $page] : [])]],
        ], ['posts' => ['page' => $page, 'perPage' => $perPage]]);

        $listing = $this->postsPage($collections['posts'], $page, $perPage);

        return [
            'hero' => $collections['hero']['items'],
            'postsTop' => $collections['postsTop']['items'],
            'posts' => $listing['posts'],
            'total' => $listing['total'],
            'totalPages' => $listing['totalPages'],
        ];
    }

    /**
     * Retrieves populated navigation categories excluding uncategorized entries.
     *
     * @return list<array<string, mixed>>
     */
    public function getCategories(): array
    {
        return array_values(array_filter(
            $this->request('categories', ['per_page' => 100, 'hide_empty' => true]),
            static fn (array $category): bool => ! in_array(strtolower($category['slug']), ['uncategorized', 'sin-categoria'], true),
        ));
    }

    /**
     * Finds a category by slug without converting a backend failure into a missing category.
     *
     * @return array<string, mixed>|null
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        return $this->request('categories', ['slug' => $slug])[0] ?? null;
    }

    /**
     * Retrieves posts assigned to a category with bounded page sizes.
     *
     * @return list<array<string, mixed>>
     */
    public function getPostsByCategory(int $categoryId, int $perPage = 24): array
    {
        if ($categoryId < 1 || $perPage < 1 || $perPage > 100) {
            throw new InvalidArgumentException('Invalid category or page size.');
        }

        return array_values($this->request('posts', ['categories' => $categoryId, '_embed' => 1, 'per_page' => $perPage]));
    }

    /**
     * Retrieves one category page without fetching or slicing the complete archive.
     *
     * @return array{posts: list<array<string, mixed>>, total: int, totalPages: int}
     */
    public function getCategoryPostsPage(int $categoryId, int $page = 1, int $perPage = 24): array
    {
        $this->validatePage($page, $perPage);
        if ($categoryId < 1) {
            throw new InvalidArgumentException('Invalid category.');
        }

        $collections = $this->requestCollections([
            'posts' => ['posts', [
                'categories' => $categoryId, '_embed' => 1, 'per_page' => $perPage,
                ...($page > 1 ? ['page' => $page] : []),
            ]],
        ], ['posts' => ['page' => $page, 'perPage' => $perPage]]);

        return $this->postsPage($collections['posts'], $page, $perPage);
    }

    /**
     * Rejects invalid pagination arguments before contacting WordPress.
     */
    private function validatePage(int $page, int $perPage): void
    {
        if ($page < 1 || $perPage < 1 || $perPage > 100) {
            throw new InvalidArgumentException('Invalid page or page size.');
        }
    }

    /**
     * Requires trustworthy totals instead of guessing whether another page exists.
     *
     * @param  array{items: list<array<string, mixed>>, total: ?int, totalPages: ?int}  $collection
     * @return array{posts: list<array<string, mixed>>, total: int, totalPages: int}
     */
    private function postsPage(array $collection, int $page, int $perPage): array
    {
        $total = $collection['total'];
        $totalPages = $collection['totalPages'];
        if ($total === null || $totalPages === null || $totalPages !== (int) ceil($total / $perPage)) {
            throw new WordPressApiException;
        }
        if ($page > max(1, $totalPages)) {
            throw new WordPressApiException(400, 'rest_post_invalid_page_number');
        }
        if (count($collection['items']) !== min($perPage, max(0, $total - ($page - 1) * $perPage))) {
            throw new WordPressApiException;
        }

        return ['posts' => $collection['items'], 'total' => $total, 'totalPages' => $totalPages];
    }

    /**
     * Retrieves the source article and category collections concurrently.
     *
     * @return array{category: array<string, mixed>|null, post: array<string, mixed>|null}
     */
    public function getPostAndCategoryConcurrently(string $categorySlug, string $postSlug): array
    {
        $results = $this->request([
            'category' => ['categories', ['slug' => $categorySlug]],
            'post' => ['posts', ['slug' => $postSlug, '_embed' => 1]],
        ]);

        return ['category' => $results['category'][0] ?? null, 'post' => $results['post'][0] ?? null];
    }

    /**
     * Invalidates only the configured site's cached News API collections.
     */
    public function invalidateCache(): void
    {
        $this->cache->invalidate($this->apiUrl());
    }

    /**
     * Resolves a configured HTTP(S) origin without credentials, query strings, or redirects.
     */
    private function apiUrl(): string
    {
        $baseUrl = $this->config->get('news.wordpress.base_url');
        $parts = is_string($baseUrl) ? parse_url($baseUrl) : false;

        if (! $this->config->get('news.enabled', true) || ! is_string($baseUrl) || filter_var($baseUrl, FILTER_VALIDATE_URL) === false || ! is_array($parts)
            || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])
            || ($this->config->get('app.env') === 'production' && $parts['scheme'] !== 'https')
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new WordPressApiException;
        }

        return rtrim($baseUrl, '/').'/wp-json/wp/v2';
    }

    /**
     * Applies identical timeouts and transient GET retry rules to single requests and pools.
     */
    private function http(PendingRequest $request): PendingRequest
    {
        return $request->acceptJson()->withoutRedirecting()
            ->timeout(max(1, (int) $this->config->get('news.wordpress.timeout', 10)))
            ->connectTimeout(max(1, (int) $this->config->get('news.wordpress.connect_timeout', 3)))
            ->retry(
                max(0, min(3, (int) $this->config->get('news.wordpress.retries', 1))) + 1,
                200,
                static fn (?Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && ($exception->response->status() === 429 || $exception->response->serverError())),
                throw: false,
            );
    }

    /**
     * Validates successful collection responses before allowing them into the cache.
     *
     * @return array{items: list<array<string, mixed>>, total: ?int, totalPages: ?int}
     */
    private function decode(Response $response, string $endpoint): array
    {
        $json = $response->json();

        if (! $response->successful() || ! is_array(json_decode($response->body())) || ! is_array($json) || ! array_is_list($json)) {
            $this->logger->warning('WordPress News API returned an invalid response.', ['endpoint' => $endpoint, 'status' => $response->status()]);
            throw new WordPressApiException(
                $response->status(),
                is_array($json) && is_string($json['code'] ?? null) ? $json['code'] : null,
            );
        }

        foreach ($json as $item) {
            if (! is_array($item) || ! is_int($item['id'] ?? null) || $item['id'] < 1 || ! is_string($item['slug'] ?? null)) {
                throw new WordPressApiException($response->status());
            }
        }

        return [
            'items' => $endpoint === 'posts' ? array_map($this->normalizePost(...), $json) : $json,
            'total' => $this->totalHeader($response, 'X-WP-Total'),
            'totalPages' => $this->totalHeader($response, 'X-WP-TotalPages'),
        ];
    }

    /**
     * Reads a nonnegative pagination header without silently converting missing data to zero.
     */
    private function totalHeader(Response $response, string $name): ?int
    {
        $value = filter_var($response->header($name), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return is_int($value) ? $value : null;
    }

    /**
     * Preserves the source post contract while tolerating missing optional embed data.
     *
     * @param  array<string, mixed>  $post
     * @return array<string, mixed>
     */
    private function normalizePost(array $post): array
    {
        $image = $this->text(data_get($post, '_embedded.wp:featuredmedia.0.source_url'));
        $images = $image === '' ? [] : ['full' => $image];
        $sizes = data_get($post, '_embedded.wp:featuredmedia.0.media_details.sizes', []);
        foreach (is_array($sizes) ? $sizes : [] as $name => $size) {
            if (is_string($name) && is_array($size) && is_string($size['source_url'] ?? null)) {
                $images[$name] = $size['source_url'];
            }
        }

        $categorySlug = 'uncategorized';
        $categoryName = 'Sin categoría';
        $taxonomies = data_get($post, '_embedded.wp:term', []);
        foreach (is_array($taxonomies) ? $taxonomies : [] as $terms) {
            foreach (is_array($terms) ? $terms : [] as $term) {
                if (is_array($term) && ($term['taxonomy'] ?? '') === 'category') {
                    $categorySlug = $this->text($term['slug'] ?? null, $categorySlug);
                    $categoryName = $this->text($term['name'] ?? null, $categoryName);
                    break 2;
                }
            }
        }

        $date = '';
        if (is_string($post['date'] ?? null) && $post['date'] !== '') {
            try {
                $parsedDate = CarbonImmutable::parse($post['date']);
                $parsedDate->locale('es');
                $date = $parsedDate->translatedFormat('d \\d\\e F \\d\\e Y');
            } catch (Exception) {
                $date = '';
            }
        }

        $categories = $post['categories'] ?? [];

        // NOTE: HTML remains untrusted data; rendering and sanitization belong to the presentation task.
        return [
            'id' => $post['id'],
            'slug' => $post['slug'],
            'title' => html_entity_decode(strip_tags($this->text(data_get($post, 'title.rendered'), 'Sin título')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'date' => $date,
            'author' => $this->text(data_get($post, '_embedded.author.0.name'), 'Administrador'),
            'image' => $image,
            'images' => $images,
            'excerpt' => $this->text(data_get($post, 'excerpt.rendered')),
            'contentHtml' => $this->text(data_get($post, 'content.rendered')),
            'categories' => is_array($categories) ? array_values(array_filter($categories, is_int(...))) : [],
            'category_slug' => $categorySlug,
            'category_name' => $categoryName,
            'yoast_head_json' => is_array($post['yoast_head_json'] ?? null) ? $post['yoast_head_json'] : null,
            'views_count' => is_numeric($post['views_count'] ?? null) ? max(0, (int) $post['views_count']) : 0,
            'is_featured' => filter_var($post['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Accepts strings from optional external fields without unsafe coercion.
     */
    private function text(mixed $value, string $fallback = ''): string
    {
        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
