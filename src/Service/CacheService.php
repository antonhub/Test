<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheService
{
    private const CACHE_EXPIRATION_TIME = 3600;
    private readonly FilesystemAdapter $cache;

    public function __construct() {
        $this->cache = new FilesystemAdapter;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $cacheKey, $value): void
    {
        $this->cache
            ->getItem($cacheKey)
            ->set($value)
            ->expiresAfter(self::CACHE_EXPIRATION_TIME);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $cacheKey): mixed
    {
        $cacheItem = $this->cache->getItem($cacheKey);

        if ( $cacheItem->isHit() ) {
            return null;
        }

        return $cacheItem->get();
    }
}