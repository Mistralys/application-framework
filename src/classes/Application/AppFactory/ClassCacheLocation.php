<?php

declare(strict_types=1);

namespace Application\AppFactory;

use Application\CacheControl\BaseCacheLocation;

class ClassCacheLocation extends BaseCacheLocation
{
    public function getByteSize(): int
    {
        return ClassCacheHandler::getCacheFolder()->getSize();
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
