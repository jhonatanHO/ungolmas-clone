<?php

declare(strict_types=1);

namespace Ugm\News\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Exceptions\WordPressApiException;

/**
 * Prepares cached navigation categories exclusively for the current News request.
 */
final class HeadlessNews
{
    /**
     * Injects the content port without depending on the concrete WordPress adapter.
     */
    public function __construct(private readonly NewsRepository $news) {}

    /**
     * Provides optional navigation without changing global configuration or view state.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $categories = $this->news->getCategories();
        } catch (WordPressApiException) {
            $categories = [];
        }

        $request->attributes->set('news.categories', $categories);

        return $next($request);
    }
}
