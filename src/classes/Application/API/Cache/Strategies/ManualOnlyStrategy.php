<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

use Application\API\Cache\APICacheStrategyInterface;
use AppUtils\FileHelper\JSONFile;

/**
 * Cache strategy that never expires a cache file automatically.
 * Cache entries are only invalidated through explicit calls to
 * {@see CacheableAPIMethodTrait::invalidateCache()}.
 *
 * @package API
 * @subpackage Cache
 */
class ManualOnlyStrategy implements APICacheStrategyInterface
{
    public const string STRATEGY_ID = 'ManualOnly';

    public function getID() : string
    {
        return self::STRATEGY_ID;
    }

    public function isCacheFileValid(JSONFile $cacheFile) : bool
    {
        return true;
    }
}
