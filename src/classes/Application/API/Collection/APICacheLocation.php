<?php
/**
 * @package API
 * @subpackage Method Collection
 */

declare(strict_types=1);

namespace Application\API\Collection;

use Application\CacheControl\BaseCacheLocation;
use Application\CacheControl\RegeneratingCacheLocationInterface;
use AppUtils\FileHelper\JSONFile;

/**
 * Cache location description class for the API method index,
 * so it can be handled via the cache control system.
 *
 * The index is a runtime-required, committed artefact: clearing
 * it would leave the application without a method index until the
 * next explicit build, so it implements {@see RegeneratingCacheLocationInterface}
 * to be rebuilt in place by the cache control system instead of
 * being deleted.
 *
 * @package API
 * @subpackage Method Collection
 */
class APICacheLocation extends BaseCacheLocation implements RegeneratingCacheLocationInterface
{
    public const string CACHE_ID = 'APIMethodIndex';
    private APIMethodIndex $index;

    public function __construct(APIMethodIndex $index)
    {
        $this->index = $index;
    }

    public function getID(): string
    {
        return self::CACHE_ID;
    }

    public function getByteSize(): int
    {
        $file = $this->getFile();

        if($file->exists()) {
            return $file->getSize();
        }

        return 0;
    }

    public function getLabel(): string
    {
        return t('API Method Index');
    }

    /**
     * Deletes the index file, without rebuilding it.
     *
     * > NOTE: This is only called directly by consumers that bypass
     * > the cache manager. {@see \Application\CacheControl\CacheManager}
     * > never calls this method on this class, using {@see self::regenerate()}
     * > instead, since this is a {@see RegeneratingCacheLocationInterface}.
     */
    public function clear(): void
    {
        $this->getFile()->delete();
    }

    /**
     * Rebuilds the index file in place, and resets the in-memory
     * index cache so the next read picks up the freshly built data.
     *
     * @throws \Application\API\APIException On build failures (e.g. unknown declared rights).
     */
    public function regenerate(): void
    {
        $this->index->build()->clearIndexCache();
    }

    private function getFile(): JSONFile
    {
        return $this->index->getDataFile();
    }
}
