<?php

declare(strict_types=1);

namespace Ugm\News\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Renders accessible share links and an instance-scoped copy interaction.
 */
final class SharePage extends Component
{
    /**
     * Accepts the canonical Core URL and a plain editorial title.
     */
    public function __construct(public string $url = '', public string $title = '') {}

    /**
     * Encodes share parameters without relying on the legacy application's JavaScript.
     */
    public function render(): View
    {
        $url = $this->url !== '' ? $this->url : url()->current();
        $encodedUrl = rawurlencode($url);
        $encodedTitle = rawurlencode($this->title);

        return view('news::components.share-page', [
            'shareUrl' => $url,
            'links' => [
                ['name' => 'Facebook', 'url' => 'https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl],
                ['name' => 'X', 'url' => 'https://twitter.com/intent/tweet?url='.$encodedUrl.'&text='.$encodedTitle],
                ['name' => 'WhatsApp', 'url' => 'https://api.whatsapp.com/send?text='.$encodedTitle.'%20'.$encodedUrl],
                ['name' => 'Telegram', 'url' => 'https://t.me/share/url?url='.$encodedUrl.'&text='.$encodedTitle],
            ],
        ]);
    }
}
