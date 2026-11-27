<?php

declare(strict_types=1);

namespace Ugm\News\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Exceptions\WordPressApiException;
use Ugm\News\Services\NewsPresentationService;

/**
 * Serves the public News pages without coupling them to the legacy application shell.
 */
final class NewsController
{
    /**
     * Injects the content port and safe presentation helpers.
     */
    public function __construct(
        private readonly NewsRepository $news,
        private readonly NewsPresentationService $presentation,
    ) {}

    /**
     * Displays the featured homepage with safe fallbacks for sparse editorial results.
     */
    public function index(Request $request): Response
    {
        $page = $this->pageNumber($request);
        $perPage = $this->listingPageSize($request, 40);
        $url = route('news.index', $page > 1 ? ['page' => $page] : []);

        try {
            $results = $this->news->getIndexPosts($page, $perPage);
        } catch (WordPressApiException $exception) {
            abort_if($exception->upstreamStatus === 400 && $exception->upstreamCode === 'rest_post_invalid_page_number', 404);

            return $this->unavailable($url)->setVary(['User-Agent', 'Sec-CH-UA-Mobile'], false);
        }

        $posts = $this->presentation->posts($results['posts'] ?? []);
        $featured = $this->presentation->posts($results['postsTop'] ?? []);
        $hero = $featured[0] ?? $this->presentation->posts($results['hero'] ?? [])[0] ?? $posts[0] ?? null;

        return response()->view('news::index', [
            'posts' => $posts,
            'pagination' => new LengthAwarePaginator($posts, $results['total'], $perPage, $page, ['path' => route('news.index')]),
            'featured' => array_values(array_filter($featured, static fn (array $post): bool => $post['id'] !== ($hero['id'] ?? null))),
            'hero' => $hero,
            'wpCategories' => $request->attributes->get('news.categories', []),
            'seo' => $this->presentation->seo(
                'Noticias deportivas | UNGOLMAS',
                'Actualidad deportiva, fútbol y las últimas noticias en UNGOLMAS.',
                $url,
                $hero['image'] ?? null,
            ),
        ])->setVary(['User-Agent', 'Sec-CH-UA-Mobile'], false);
    }

    /**
     * Limits smartphone listings while preserving each archive's desktop page size.
     */
    private function listingPageSize(Request $request, int $desktopSize): int
    {
        $mobile = match ($request->header('Sec-CH-UA-Mobile')) {
            '?1' => true,
            '?0' => false,
            default => preg_match('/Mobile|iPhone|iPod|IEMobile|Opera Mini/i', $request->userAgent() ?? '') === 1,
        };

        return $mobile ? 12 : $desktopSize;
    }

    /**
     * Displays a category or returns a genuine 404 when it does not exist.
     */
    public function category(Request $request, string $categorySlug): Response
    {
        $page = $this->pageNumber($request);
        $perPage = $this->listingPageSize($request, 24);
        $url = route('news.category', ['category_slug' => $categorySlug, ...($page > 1 ? ['page' => $page] : [])]);

        try {
            $category = $this->news->getCategoryBySlug($categorySlug);
            abort_if($category === null, 404);
            $listing = $this->news->getCategoryPostsPage((int) $category['id'], $page, $perPage);
            $posts = $listing['posts'];
        } catch (WordPressApiException $exception) {
            abort_if($exception->upstreamStatus === 400 && $exception->upstreamCode === 'rest_post_invalid_page_number', 404);

            return $this->unavailable($url)->setVary(['User-Agent', 'Sec-CH-UA-Mobile'], false);
        }

        $name = $this->presentation->text($category['name'] ?? null, 'Noticias');

        return response()->view('news::categories', [
            'category' => $category,
            'posts' => $posts,
            'pagination' => new LengthAwarePaginator($posts, $listing['total'], $perPage, $page, ['path' => route('news.category', $categorySlug)]),
            'wpCategories' => $request->attributes->get('news.categories', []),
            'seo' => $this->presentation->seo(
                $name.' | Noticias UNGOLMAS',
                $this->presentation->text($category['description'] ?? null, 'Las últimas noticias de '.$name.'.'),
                $url,
            ),
        ])->setVary(['User-Agent', 'Sec-CH-UA-Mobile'], false);
    }

    /**
     * Displays a sanitized article only when it belongs to the requested category.
     */
    public function show(Request $request, string $categorySlug, string $slug): View|Response
    {
        try {
            $result = $this->news->getPostAndCategoryConcurrently($categorySlug, $slug);
        } catch (WordPressApiException) {
            return $this->unavailable();
        }

        $post = $result['post'];
        $category = $result['category'];
        abort_if($post === null || $category === null, 404);
        abort_unless(in_array((int) $category['id'], $post['categories'] ?? [], true), 404);

        try {
            $popularPosts = $this->presentation->posts($this->news->request('posts', [
                '_embed' => 1, 'per_page' => 8, 'orderby' => 'views', 'order' => 'desc',
            ]));
        } catch (WordPressApiException) {
            $popularPosts = [];
        }

        return view('news::single', [
            'post' => $post,
            'category' => $category,
            'contentHtml' => $this->presentation->content(is_string($post['contentHtml'] ?? null) ? $post['contentHtml'] : ''),
            'popularPosts' => $popularPosts,
            'wpCategories' => $request->attributes->get('news.categories', []),
            'seo' => $this->presentation->seo(
                $this->presentation->text(data_get($post, 'yoast_head_json.title'), $this->presentation->text($post['title'] ?? null).' | UNGOLMAS'),
                $this->presentation->text(data_get($post, 'yoast_head_json.description'), $this->presentation->text($post['excerpt'] ?? null)),
                route('news.post', ['category_slug' => $categorySlug, 'slug' => $slug]),
                data_get($post, 'yoast_head_json.og_image.0.url') ?? $post['image'] ?? null,
            ),
        ]);
    }

    /**
     * Redirects the source plural article URL to the task's canonical singular URL.
     */
    public function legacy(string $categorySlug, string $slug): RedirectResponse
    {
        return redirect()->route('news.post', ['category_slug' => $categorySlug, 'slug' => $slug], 301);
    }

    /**
     * Renders an accessible upstream-error page without exposing backend details.
     */
    private function unavailable(?string $retryUrl = null): Response
    {
        return response()->view('news::unavailable', [
            'wpCategories' => [],
            'retryUrl' => $retryUrl ?? route('news.index'),
            'seo' => $this->presentation->seo('Noticias no disponibles | UNGOLMAS', 'El servicio de noticias no está disponible temporalmente.', route('news.index')),
        ], 502);
    }

    /**
     * Rejects malformed or nonpositive page numbers rather than silently serving page one.
     */
    private function pageNumber(Request $request): int
    {
        $value = $request->query->has('page') ? $request->query('page') : '1';
        abort_unless(is_string($value) && preg_match('/^[1-9][0-9]*$/D', $value) === 1, 404);
        $page = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_unless(is_int($page), 404);

        return $page;
    }
}
