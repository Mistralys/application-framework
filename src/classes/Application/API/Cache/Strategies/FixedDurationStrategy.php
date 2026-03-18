<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

use Application\API\Cache\APICacheStrategyInterface;
use AppUtils\FileHelper\JSONFile;

/**
 * Cache strategy that considers a cache file valid as long as its modification
 * time is within the configured duration from the current time.
 *
 * @package API
 * @subpackage Cache
 */
class FixedDurationStrategy implements APICacheStrategyInterface
{
    public const string STRATEGY_ID = 'FixedDuration';

    public const int DURATION_1MIN = 60;
    public const int DURATION_5MIN = 300;
    public const int DURATION_15MIN = 900;
    public const int DURATION_1HOUR = 3600;
    public const int DURATION_6HOURS = 21600;
    public const int DURATION_12HOURS = 43200;
    public const int DURATION_24HOURS = 86400;

    private int $durationInSeconds;

    public function __construct(int $durationInSeconds = self::DURATION_1HOUR)
    {
        $this->durationInSeconds = $durationInSeconds;
    }

    public function getID() : string
    {
        return self::STRATEGY_ID;
    }

    /**
     * Returns true if the cache file's modification time is within
     * the configured duration from the current time.
     * Returns false if `filemtime()` returns false (e.g. race condition
     * during parallel deletion — treated as expired).
     *
     * @param JSONFile $cacheFile
     * @return bool
     */
    public function isCacheFileValid(JSONFile $cacheFile) : bool
    {
        $mtime = filemtime($cacheFile->getPath());

        if($mtime === false)
        {
            return false;
        }

        return (time() - $mtime) < $this->durationInSeconds;
    }
}
