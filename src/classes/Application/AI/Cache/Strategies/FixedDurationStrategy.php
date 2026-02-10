<?php

declare(strict_types=1);

namespace Application\AI\Cache\Strategies;

use Application\AI\Cache\BaseAICacheStrategy;
use AppUtils\FileHelper\JSONFile;

class FixedDurationStrategy extends BaseAICacheStrategy
{
    public const string STRATEGY_ID = 'fixed_duration';

    public const int DURATION_1_HOUR = 3600;
    public const int DURATION_6_HOURS = 21600;
    public const int DURATION_12_HOURS = 43200;
    public const int DURATION_24_HOURS = 86400;

    private int $durationInSeconds;

    public function __construct(int $durationInSeconds=self::DURATION_1_HOUR)
    {
        $this->durationInSeconds = $durationInSeconds;
    }

    public function getID(): string
    {
        return self::STRATEGY_ID;
    }

    protected function isCacheFileValid(JSONFile $cacheFile): bool
    {
        return (time() - filemtime($cacheFile->getPath()) < $this->durationInSeconds);
    }
}
