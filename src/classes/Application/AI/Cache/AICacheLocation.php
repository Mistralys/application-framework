<?php

declare(strict_types=1);

namespace Application\AI\Cache;

use Application\AI\Cache\Strategies\FixedDurationStrategy;
use Application\CacheControl\BaseCacheLocation;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;

class AICacheLocation extends BaseCacheLocation
{
    public const string LOCATION_ID = 'ai_tools_cache';

    private FolderInfo $cacheFolder;

    public function __construct()
    {
        $this->cacheFolder = new FixedDurationStrategy()->getCacheFolder();
    }

    private static ?AICacheLocation $instance = null;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new AICacheLocation();
        }

        return self::$instance;
    }

    public function getByteSize(): int
    {
        return $this->cacheFolder->getSize();
    }

    public function getLabel(): string
    {
        return t('AI Tools Cache');
    }

    public function clear(): void
    {
        if($this->cacheFolder->exists()) {
            FileHelper::deleteTree($this->cacheFolder);
        }
    }

    public function getID(): string
    {
        return self::LOCATION_ID;
    }
}
