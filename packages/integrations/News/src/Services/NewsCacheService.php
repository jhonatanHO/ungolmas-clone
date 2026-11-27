<?php

declare(strict_types=1);

namespace Ugm\News\Services;

use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;

/**
 * Caches API reads in an isolated site namespace across tagged and untagged stores.
 */
final class NewsCacheService
{
    /**
     * Injects Laravel's configured cache and configuration repositories.
     */
    public function __construct(
        private readonly CacheManager $cache,
        private readonly ConfigRepository $config,
    ) {}

    /**
     * Reuses individual API responses across named batches without caching partial failures.
     *
     * @param  array<string, array{0: string, 1?: array<string, mixed>}>  $requests
     * @param  Closure(array<string, array{0: string, 1?: array<string, mixed>}>): array<string, array<mixed>>  $loader
     * @return array<string, array<mixed>>
     */
    public function remember(string $apiUrl, array $requests, Closure $loader): array
    {
        $ttl = (int) $this->config->get('news.cache.ttl', 18000);
        $store = $ttl > 0 ? $this->store() : null;
        $generation = null;

        if ($store !== null) {
            $namespace = $this->namespaceKey($apiUrl);
            $store->add($namespace, Str::uuid()->toString(), 315360000);
            $generation = $store->get($namespace);
        }

        $representatives = [];
        $aliases = [];
        $keys = [];
        $missing = [];
        $results = [];

        foreach ($requests as $name => $request) {
            $identity = hash('sha256', serialize([
                $apiUrl.'/'.$request[0], $this->canonicalize($request[1] ?? []),
            ]));

            if (isset($representatives[$identity])) {
                $aliases[$name] = $representatives[$identity];

                continue;
            }

            $representatives[$identity] = $name;
            $aliases[$name] = $name;
            $keys[$name] = 'ugm:news:read:v3:'.hash('sha256', serialize([$identity, $generation]));
            $cached = $store?->get($keys[$name]);

            if (is_array($cached)) {
                $results[$name] = $cached;
            } else {
                $missing[$name] = $request;
            }
        }

        if ($missing !== []) {
            // NOTE: Validate the complete missing batch before writing any of its responses.
            $loaded = $loader($missing);

            foreach ($missing as $name => $request) {
                $results[$name] = $loaded[$name];
                // NOTE: Keys capture the old generation if invalidation occurs during loading.
                $store?->put($keys[$name], $loaded[$name], $ttl);
            }
        }

        $output = [];
        foreach ($aliases as $name => $representative) {
            $output[$name] = $results[$representative];
        }

        return $output;
    }

    /**
     * Rotates the site's namespace without flushing another package's cache.
     */
    public function invalidate(string $apiUrl): void
    {
        $this->store()->forever($this->namespaceKey($apiUrl), Str::uuid()->toString());
    }

    /**
     * Resolves the optional package-specific cache store.
     */
    private function store(): CacheRepository
    {
        return $this->cache->store($this->config->get('news.cache.store'));
    }

    /**
     * Separates sites and normalization versions within the News cache.
     */
    private function namespaceKey(string $apiUrl): string
    {
        return 'ugm:news:generation:v3:'.hash('sha256', $apiUrl);
    }

    /**
     * Sorts associative keys and removes cache-busters without reordering lists.
     */
    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            unset($value['_']);
            ksort($value);
        }

        return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
    }
}
