<?php

declare(strict_types=1);

namespace Ugm\News\Contracts;

use Ugm\News\Exceptions\WordPressApiException;

/**
 * Defines the read-only WordPress content integration port.
 */
interface NewsRepository
{
    /**
     * Retrieves a collection or a named batch of API collections.
     *
     * @param  string|array<string, array{0: string, 1?: array<string, mixed>}>  $requests
     * @param  array<string, mixed>  $params
     * @return array<mixed>
     *
     * @throws WordPressApiException
     */
    public function request(string|array $requests, array $params = []): array;

    /**
     * Retrieves the homepage collections using the source editorial filters.
     *
     * @return array<mixed>
     */
    public function getIndexPosts(int $page = 1, int $perPage = 40): array;

    /**
     * Retrieves up to one hundred populated navigation categories.
     *
     * @return list<array<string, mixed>>
     */
    public function getCategories(): array;

    /**
     * Finds a category by its exact slug.
     *
     * @return array<string, mixed>|null
     */
    public function getCategoryBySlug(string $slug): ?array;

    /**
     * Retrieves the posts assigned to a category.
     *
     * @return list<array<string, mixed>>
     */
    public function getPostsByCategory(int $categoryId, int $perPage = 24): array;

    /**
     * Retrieves category articles and the totals returned by WordPress.
     *
     * @return array{posts: list<array<string, mixed>>, total: int, totalPages: int}
     */
    public function getCategoryPostsPage(int $categoryId, int $page = 1, int $perPage = 24): array;

    /**
     * Retrieves a post and its category concurrently.
     *
     * @return array{category: array<string, mixed>|null, post: array<string, mixed>|null}
     */
    public function getPostAndCategoryConcurrently(string $categorySlug, string $postSlug): array;

    /**
     * Invalidates only this WordPress site's integration cache.
     */
    public function invalidateCache(): void;
}
