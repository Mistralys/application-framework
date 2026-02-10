<?php

declare(strict_types=1);

namespace Application\AI\Cache;

use Application\AI\Tools\AIToolInterface;
use Application\Application;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;

abstract class BaseAICacheStrategy implements AICacheStrategyInterface
{
    private static ?FolderInfo $cacheFolder = null;

    public function getCacheFolder() : FolderInfo
    {
        if(!isset(self::$cacheFolder)) {
            self::$cacheFolder = FolderInfo::factory(Application::getCacheFolder().'/ai_cache')->create();
        }

        return self::$cacheFolder;
    }

    public function getToolCacheFile(AIToolInterface $tool) : JSONFile
    {
        return JSONFile::factory($this->getCacheFolder().'/'.$tool->getID().'.json');
    }

    public function isCacheValid(AIToolInterface $tool): bool
    {
        $file = $this->getToolCacheFile($tool);

        return $file->exists() && $this->isCacheFileValid($file);
    }

    abstract protected function isCacheFileValid(JSONFile $cacheFile) : bool;

    public function getFromCache(AIToolInterface $tool): array
    {
        return $this->getToolCacheFile($tool)->parse();
    }

    public function saveCache(AIToolInterface $tool, array $data): AICacheStrategyInterface
    {
        $this->getToolCacheFile($tool)->putData($data);
        return $this;
    }
}