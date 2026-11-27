<?php

declare(strict_types=1);

namespace Ugm\News\Services;

use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Prepares safe editorial content and local links for the Blade presentation layer.
 */
final class NewsPresentationService
{
    private readonly HtmlSanitizer $sanitizer;

    /**
     * Configures a maintained HTML allowlist without scripts, styles, or arbitrary embeds.
     */
    public function __construct()
    {
        $this->sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->allowMediaSchemes(['http', 'https'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->forceAttribute('img', 'loading', 'lazy')
                ->forceAttribute('img', 'referrerpolicy', 'no-referrer')
                ->withMaxInputLength(500000),
        );
    }

    /**
     * Sanitizes remote article markup before it is rendered without Blade escaping.
     */
    public function content(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }

    /**
     * Converts optional editorial fields to plain display text.
     */
    public function text(mixed $value, string $fallback = ''): string
    {
        if (! is_string($value)) {
            return $fallback;
        }

        return Str::squish(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?: $fallback;
    }

    /**
     * Allows only absolute HTTP(S) URLs for editorial images and social metadata.
     */
    public function imageUrl(mixed $value): ?string
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($value);

        return is_array($parts) && in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            && ! isset($parts['user']) && ! isset($parts['pass']) ? $value : null;
    }

    /**
     * Generates the canonical Core article link instead of a WordPress permalink.
     *
     * @param  array<string, mixed>  $post
     */
    public function postUrl(array $post): string
    {
        return route('news.post', [
            'category_slug' => $this->text($post['category_slug'] ?? null, 'uncategorized'),
            'slug' => $this->text($post['slug'] ?? null),
        ]);
    }

    /**
     * Prepares escaped metadata inputs with a local canonical URL.
     *
     * @return array{title: string, description: string, image: ?string, url: string}
     */
    public function seo(string $title, string $description, string $url, mixed $image = null): array
    {
        return [
            'title' => $this->text($title, 'Noticias deportivas | UNGOLMAS'),
            'description' => Str::limit($this->text($description), 160, ''),
            'image' => $this->imageUrl($image),
            'url' => $url,
        ];
    }

    /**
     * Normalizes an optional collection without assuming four featured posts exist.
     *
     * @return list<array<string, mixed>>
     */
    public function posts(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, static fn (mixed $item): bool => is_array($item)
            && is_int($item['id'] ?? null) && is_string($item['slug'] ?? null)));
    }
}
