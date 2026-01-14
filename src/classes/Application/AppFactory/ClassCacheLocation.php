<?php

declare(strict_types=1);

namespace Application\AppFactory;

use Application\CacheControl\BaseCacheLocation;

class ClassCacheLocation extends BaseCacheLocation
{
    public const string CACHE_ID = 'DynamicClassCache';

    public function getID(): string
    {
        return self::CACHE_ID;
    }

    public function getByteSize(): int
    {
        return ClassCacheHandler::getCacheSize();
    }

    public function getLabel(): string
    {
        return t('PHP class cache');
    }

    public function clear(): void
    {
        ClassCacheHandler::clearClassCache();
    }
}
