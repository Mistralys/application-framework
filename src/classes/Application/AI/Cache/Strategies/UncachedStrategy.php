<?php

declare(strict_types=1);

namespace Application\AI\Cache\Strategies;

use Application\AI\Cache\BaseAICacheStrategy;
use AppUtils\FileHelper\JSONFile;

class UncachedStrategy extends BaseAICacheStrategy
{
    public const string STRATEGY_ID = 'uncached';

    public function getID(): string
    {
        return self::STRATEGY_ID;
    }

    protected function isCacheFileValid(JSONFile $cacheFile): bool
    {
        return false;
    }
}
