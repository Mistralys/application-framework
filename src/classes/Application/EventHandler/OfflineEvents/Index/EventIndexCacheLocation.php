<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents\Index;

use Application\CacheControl\BaseCacheLocation;
use Application\CacheControl\RegeneratingCacheLocationInterface;
use AppUtils\FileHelper\PHPFile;

/**
 * Cache location description class for the offline events index,
 * so it can be handled via the cache control system.
 *
 * The index is a runtime-required, committed artefact: clearing
 * it would leave the application without an offline event/listener
 * index until the next explicit build, so it implements
 * {@see RegeneratingCacheLocationInterface} to be rebuilt in place
 * by the cache control system instead of being deleted.
 *
 * @package Application
 * @subpackage EventHandler
 */
class EventIndexCacheLocation extends BaseCacheLocation implements RegeneratingCacheLocationInterface
{
    public const string CACHE_ID = 'OfflineEventIndex';

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
        return t('Offline Event Index');
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
     * Rebuilds the index file in place via {@see EventIndexer::reindex()},
     * bypassing its once-per-process guard, and resets the {@see EventIndex}
     * singleton's in-memory cache so the next read picks up the freshly
     * built data.
     *
     * @throws \Throwable On build failures.
     */
    public function regenerate(): void
    {
        EventIndexer::getInstance()->reindex();

        EventIndex::getInstance()->clearIndexCache();
    }

    private function getFile(): PHPFile
    {
        return EventIndex::getIndexFile();
    }
}
