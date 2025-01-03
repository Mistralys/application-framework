<?php
/**
 * @package Application
 * @subpackage AppFactory
 */

declare(strict_types=1);

namespace Application\AppFactory;

use Application;
use Application\AppFactory;
use AppUtils\ClassHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\SerializedFile;

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
     * @var array<string,array<int,string>>
     */
    private static array $classCache = array();

    /**
     * Uses the class helper to find classes in the target folder.
     * The results are cached to avoid unnecessary file system
     * accesses. The cache uses the application version as a key, so
     * it is automatically invalidated when the application is updated.
     *
     * NOTE: The cache is automatically disabled in development mode.
     *
     * @param FolderInfo $folder
     * @param bool $recursive
     * @param string|null $baseClass
     * @return class-string[]
     */
    public static function findClassesInFolder(FolderInfo $folder, bool $recursive, ?string $baseClass=null) : array
    {
        $cacheKey = sprintf(
            'classes_v%s_%s',
            md5($folder->getPath().bool2string($recursive).$baseClass),
            // Important not to use the Driver instance, as it may not be initialized yet
            AppFactory::createVersionInfo()->getFullVersion()
        );

        if(isset(self::$classCache[$cacheKey])) {
            return self::$classCache[$cacheKey];
        }

        $cacheFile = SerializedFile::factory(self::getCacheFolder()->create().'/'.$cacheKey.'.ser');

        if($cacheFile->exists() && self::isCacheEnabled()) {
            self::$classCache[$cacheKey] = $cacheFile->parse();
            return self::$classCache[$cacheKey];
        }

        self::$classCache[$cacheKey] = array();

        foreach(ClassHelper::findClassesInFolder($folder, $recursive, $baseClass) as $classInfo) {
            self::$classCache[$cacheKey][] = $classInfo->getNameNS();
        }

        // Ensure consistent order
        sort(self::$classCache[$cacheKey]);

        $cacheFile->putData(self::$classCache[$cacheKey]);

        return self::$classCache[$cacheKey];
    }

    public static function getCacheFolder() : FolderInfo
    {
        return FolderInfo::factory(Application::getStorageSubfolderPath('class-cache'));
    }

    public static function clearClassCache() : void
    {
        FileHelper::deleteTree(self::getCacheFolder());
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
}
