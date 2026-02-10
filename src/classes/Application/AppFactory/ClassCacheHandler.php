<?php
/**
 * @package Application
 * @subpackage AppFactory
 */

declare(strict_types=1);

namespace Application\AppFactory;

use Application\Application;
use AppLocalize\Localization;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;

/**
 * Handles the caching of class information to speed up class
 * discovery operations.
 *
 * ## Usage
 *
 * - Finding classes in a folder: {@see self::findClassesInFolder()}
 * - Clearing the class cache: {@see self::clearClassCache()}
 * - Turning the cache on or off: {@see self::setCacheEnabled()}
 *
 * @package Application
 * @subpackage AppFactory
 */
class ClassCacheHandler
{
    /**
     * Uses the class helper to find classes in the target folder.
     * The results are cached to avoid unnecessary file system
     * accesses. The cache uses the application version as a key, so
     * it is automatically invalidated when the application is updated.
     *
     * > NOTE: The cache is automatically disabled in development mode.
     *
     * @param FolderInfo $folder
     * @param bool $recursive
     * @param string|null $baseClass
     * @return class-string[]
     */
    public static function findClassesInFolder(FolderInfo $folder, bool $recursive=false, ?string $baseClass=null) : array
    {
        if(self::isCacheEnabled()) {
            return ClassHelper::getRepositoryManager()->findClassesInFolder($folder, $recursive, $baseClass)->getClasses();
        }

        $result = array();
        foreach(ClassHelper::findClassesInFolder($folder, $recursive, $baseClass) as $classInfo) {
            $result[] = $classInfo->getNameNS();
        }

        return $result;
    }

    public static function getCacheFolder() : FolderInfo
    {
        return FolderInfo::factory(Application::getStorageSubfolderPath('class-cache'));
    }

    public static function clearClassCache() : void
    {
        foreach(self::getRepositories() as $repository) {
            $repository->clearCache();
        }
    }

    private static ?bool $enabled = null;

    /**
     * @param bool|null $enabled Use NULL to auto-detect based on the user and environment. See {@see self::isCacheEnabled()}.
     * @return void
     */
    public static function setCacheEnabled(?bool $enabled) : void
    {
        self::$enabled = $enabled;
    }

    public static function isCacheEnabled() : bool
    {
        return self::$enabled === true || !isDevelMode() || !Application::isUnitTestingRunning();
    }

    private static ?ClassCacheLocation $cacheLocation = null;

    /**
     * Gets the cache location instance for the folder where
     * the class cache files are stored, for use with the
     * {@see Application\CacheControl\CacheManager}.
     *
     * @return ClassCacheLocation
     * @see Application\OfflineEvents\RegisterCacheLocationsEvent\RegisterClassCacheListener::handleEvent()
     */
    public static function getCacheLocation() : ClassCacheLocation
    {
        if(self::$cacheLocation === null) {
            self::$cacheLocation = new ClassCacheLocation();
        }

        return self::$cacheLocation;
    }

    public static function getCacheSize() : int
    {
        $size = 0;
        foreach(self::getCacheFiles() as $file) {
            if($file->exists()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * @return FileInfo[]
     */
    public static function getCacheFiles() : array
    {
        $result = array();
        foreach(self::getRepositories() as $repository) {
            $result[] = $repository->getCacheFile();
        }

        return $result;
    }

    /**
     * @return ClassRepositoryManager[]
     */
    public static function getRepositories() : array
    {
        return array(
            ClassHelper::getRepositoryManager(),
            Localization::getClassRepository()
        );
    }
}
