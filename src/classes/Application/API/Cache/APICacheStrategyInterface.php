<?php

declare(strict_types=1);

namespace Application\API\Cache;

use AppUtils\FileHelper\JSONFile;

interface APICacheStrategyInterface
{
    /**
     * Returns a unique identifier for this strategy (e.g. 'FixedDuration', 'ManualOnly').
     *
     * @return string
     */
    public function getID() : string;

    /**
     * Given a cache file, returns whether it is still considered valid.
     *
     * @param JSONFile $cacheFile
     * @return bool
     */
    public function isCacheFileValid(JSONFile $cacheFile) : bool;
}
