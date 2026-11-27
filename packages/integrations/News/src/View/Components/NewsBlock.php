<?php

declare(strict_types=1);

namespace Ugm\News\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Ugm\News\Services\NewsPresentationService;

/**
 * Renders a reusable source-compatible editorial card using Tailwind utilities.
 */
final class NewsBlock extends Component
{
    public string $variant;

    public string $as;

    /**
     * Validates layout and heading variants without allowing arbitrary HTML tags.
     *
     * @param  array<string, mixed>  $post
     */
    public function __construct(
        private readonly NewsPresentationService $presentation,
        public array $post,
        string $variant = 'box',
        string $as = 'h3',
        public bool $showExcerpt = false,
        public bool $shouldRenderLink = true,
    ) {
        $this->variant = in_array($variant, ['hero', 'big', 'box', 'single-hero'], true) ? $variant : 'box';
        $this->as = in_array($as, ['h1', 'h2', 'h3', 'h4'], true) ? $as : 'h3';
    }

    /**
     * Provides complete class names for Tailwind's source scanner.
     */
    public function render(): View
    {
        return view('news::components.news-block', [
            'permalink' => $this->presentation->postUrl($this->post),
            'linked' => $this->shouldRenderLink && $this->variant !== 'single-hero',
            'title' => $this->presentation->text($this->post['title'] ?? null, 'Sin título'),
            'excerpt' => $this->variant === 'single-hero'
                ? $this->presentation->text($this->post['excerpt'] ?? null)
                : Str::limit($this->presentation->text($this->post['excerpt'] ?? null), 200),
            'layoutClass' => match ($this->variant) {
                'hero' => 'flex-col gap-[10px] md:flex-row-reverse md:items-stretch',
                'single-hero' => 'flex-col-reverse gap-[20px]',
                'big' => 'flex-col gap-[30px]',
                default => 'flex-row gap-[10px] md:flex-col',
            },
            'headingClass' => match ($this->variant) {
                'hero' => 'text-[30px] leading-[36px] md:text-[36px] md:leading-[42px]',
                'single-hero' => 'text-[30px] leading-[36px] md:text-[48px] md:leading-[54px]',
                'big' => 'text-[30px] leading-[36px]',
                default => 'text-[16px] leading-[22px]',
            },
        ]);
    }
}
