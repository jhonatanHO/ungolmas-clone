<?php

declare(strict_types=1);

namespace Ugm\News\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Ugm\News\Services\NewsPresentationService;

/**
 * Renders responsive editorial pictures with native loading and a local SVG fallback.
 */
final class NewsImage extends Component
{
    /**
     * Accepts normalized content without requiring every WordPress image size.
     *
     * @param  array<string, mixed>  $post
     */
    public function __construct(
        private readonly NewsPresentationService $presentation,
        public array $post,
        public string $variant = 'box',
    ) {}

    /**
     * Selects the source image sizes and preserves each card's aspect ratio.
     */
    public function render(): View
    {
        [$desktop, $mobile] = match ($this->variant) {
            'single-hero' => ['rectangle-3xl', 'rectangle-xl'],
            'hero' => ['rectangle-3xl', 'rectangle-lg'],
            'big' => ['rectangle-xl', 'rectangle-xl'],
            default => ['rectangle-md', 'square-sm'],
        };

        $fallback = $this->presentation->imageUrl($this->post['image'] ?? null)
            ?? $this->presentation->imageUrl(data_get($this->post, 'images.full'));

        return view('news::components.news-image', [
            'desktop' => $this->presentation->imageUrl(data_get($this->post, 'images.'.$desktop)) ?? $fallback,
            'mobile' => $this->presentation->imageUrl(data_get($this->post, 'images.'.$mobile)) ?? $fallback,
            'alt' => $this->presentation->text($this->post['title'] ?? null, 'Imagen de noticia'),
            'eager' => in_array($this->variant, ['hero', 'single-hero'], true),
            'aspectClass' => match ($this->variant) {
                'hero' => 'aspect-video md:aspect-[21/10] md:h-full',
                'big' => 'aspect-video md:aspect-[2/1]',
                'single-hero' => 'aspect-[3.7/1.8]',
                default => 'aspect-[3/2]',
            },
        ]);
    }
}
