<?php

declare(strict_types=1);

namespace Ugm\News\Console\Commands;

use Illuminate\Console\Command;
use Ugm\News\Contracts\NewsRepository;
use Ugm\News\Exceptions\WordPressApiException;

/**
 * Checks the WordPress read endpoints from the current Laravel runtime.
 */
final class CheckWordPressConnectionCommand extends Command
{
    protected $signature = 'news:check-connection';

    protected $description = 'Validate WordPress News API reads without changing remote content';

    /**
     * Checks minimal collections while preserving the configured API response cache.
     */
    public function handle(NewsRepository $news): int
    {
        try {
            foreach ([
                'categories' => ['categories', ['per_page' => 1]],
                'posts' => ['posts', ['per_page' => 1, '_embed' => 1]],
                'featured' => ['posts', ['per_page' => 1, '_embed' => 1, 'featured' => 'true']],
                'popular' => ['posts', ['per_page' => 1, '_embed' => 1, 'orderby' => 'views', 'order' => 'desc']],
            ] as $name => [$endpoint, $params]) {
                $items = $news->request($endpoint, $params);
                $this->info($name.': OK ('.count($items).' resultados)');
            }
        } catch (WordPressApiException $exception) {
            $this->error($exception->getMessage());
            if ($exception->upstreamStatus !== null) {
                $this->error('HTTP '.$exception->upstreamStatus.'. Revisar URL, disponibilidad e IP autorizada en WordPress.');
            }

            return self::FAILURE;
        }

        $this->comment('Solo GET; los resultados pueden provenir de caché. Una respuesta OK no demuestra que WordPress aplique los filtros personalizados.');

        return self::SUCCESS;
    }
}
