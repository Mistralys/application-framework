<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\AppFactory;
use Application\CacheControl\BaseCacheLocation;
use Application\CacheControl\RegeneratingCacheLocationInterface;
use AppUtils\FileHelper\PHPFile;

/**
 * Cache location description class for the admin screen index,
 * so it can be handled via the cache control system.
 *
 * The index is a runtime-required, committed artefact: clearing
 * it would leave the application without a sitemap until the
 * next explicit build, so it implements {@see RegeneratingCacheLocationInterface}
 * to be rebuilt in place by the cache control system instead of
 * being deleted.
 *
 * @package Application
 * @subpackage Admin
 */
class AdminScreenIndexCacheLocation extends BaseCacheLocation implements RegeneratingCacheLocationInterface
{
    public const string CACHE_ID = 'AdminScreenIndex';

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
        return t('Admin Screen Index');
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
     * Rebuilds the index file in place, using a fresh indexer instance,
     * and refreshes the {@see AdminScreenIndex} singleton so it serves
     * the newly rebuilt data.
     *
     * @throws \Throwable On build failures.
     */
    public function regenerate(): void
    {
        (new AdminScreenIndexer(AppFactory::createDriver()))->index();

        AdminScreenIndex::getInstance()->reload();
    }

    private function getFile(): PHPFile
    {
        return AdminScreenIndex::getIndexFile();
    }
}
