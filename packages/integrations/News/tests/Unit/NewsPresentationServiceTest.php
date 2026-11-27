<?php

declare(strict_types=1);

namespace Ugm\News\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ugm\News\Services\NewsPresentationService;

/**
 * Verifies safe remote HTML and image URL handling independently of Laravel requests.
 */
final class NewsPresentationServiceTest extends TestCase
{
    /**
     * Preserves editorial structure without script, style, or URL-based execution.
     */
    #[Test]
    public function sanitizesEditorialMarkup(): void
    {
        $html = (new NewsPresentationService)->content('<h2>Título</h2><p>Texto <strong>importante</strong></p><ul><li>Uno</li></ul><script>alert(1)</script><style>body{display:none}</style><iframe src="https://untrusted.test"></iframe><img src="https://media.test/photo.webp" onerror="alert(1)"><a href="javascript:alert(1)" onclick="alert(1)">Enlace</a>');

        self::assertStringContainsString('<h2>Título</h2>', $html);
        self::assertStringContainsString('<strong>importante</strong>', $html);
        self::assertStringContainsString('<li>Uno</li>', $html);
        self::assertStringContainsString('https://media.test/photo.webp', $html);
        self::assertStringContainsString('loading="lazy"', $html);
        foreach (['<script', '<style', '<iframe', 'javascript:', 'onerror', 'onclick', 'alert(1)'] as $unsafe) {
            self::assertStringNotContainsString($unsafe, $html);
        }
    }

    /**
     * Rejects script, data, relative, and credential-bearing image URLs.
     */
    #[Test]
    public function restrictsEditorialImageUrls(): void
    {
        $presentation = new NewsPresentationService;

        foreach (['javascript:alert(1)', 'data:image/svg+xml,test', '/relative/image.png', 'https://user:password@media.test/photo.jpg', null] as $url) {
            self::assertNull($presentation->imageUrl($url));
        }

        self::assertSame('https://media.test/photo.webp', $presentation->imageUrl('https://media.test/photo.webp'));
    }
}
