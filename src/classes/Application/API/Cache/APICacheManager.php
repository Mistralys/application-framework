<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\Application;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;

/**
 * Static utility class for managing the file system layout of
 * cached API method responses.
 *
 * Storage layout:
 * <pre>
 * {APP_STORAGE}/
 *   api/
 *     cache/
 *       {MethodName}/
 *         {hash}.json
 * </pre>
 */
class APICacheManager
{
    private const CACHE_SUBFOLDER = 'api/cache';

    /**
     * Returns the base cache folder, creating it if it does not exist.
     *
     * @return FolderInfo
     */
    public static function getCacheFolder() : FolderInfo
    {
        return FolderInfo::factory(Application::getStorageSubfolderPath(self::CACHE_SUBFOLDER));
    }

    /**
     * Returns the cache subfolder for a specific API method.
     * The folder is not created automatically; it is created on the
     * first write via {@see CacheableAPIMethodTrait::writeToCache()}.
     *
     * @param string $methodName Must be a trusted, framework-internal method
     *                           name (i.e. a value returned by
     *                           {@see APIMethodInterface::getMethodName()},
     *                           never user-supplied input). The value is
     *                           concatenated directly into a filesystem path.
     * @return FolderInfo
     * @throws APICacheException {@see APICacheException::ERROR_INVALID_METHOD_NAME}
     */
    public static function getMethodCacheFolder(string $methodName) : FolderInfo
    {
        if($methodName === '' ||
            str_contains($methodName, '/') ||
            str_contains($methodName, '..') ||
            str_contains($methodName, DIRECTORY_SEPARATOR))
        {
            throw new APICacheException(
                'Invalid API method name for cache folder.',
                sprintf('The method name [%s] is empty or contains invalid path characters.', $methodName),
                APICacheException::ERROR_INVALID_METHOD_NAME
            );
        }

        return FolderInfo::factory(
            Application::getStorageSubfolderPath(self::CACHE_SUBFOLDER) . '/' . $methodName
        );
    }

    /**
     * Deletes all cached responses for a specific API method.
     * No-op if the method's cache folder does not exist.
     *
     * @param string $methodName
     * @return void
     * @throws APICacheException {@see APICacheException::ERROR_INVALID_METHOD_NAME}
     */
    public static function invalidateMethod(string $methodName) : void
    {
        $folder = self::getMethodCacheFolder($methodName);

        if($folder->exists())
        {
            FileHelper::deleteTree($folder);
        }
    }

    /**
     * Deletes all cached API response data.
     *
     * @return void
     */
    public static function clearAll() : void
    {
        $folder = self::getCacheFolder();

        if($folder->exists())
        {
            FileHelper::deleteTree($folder);
        }
    }

    /**
     * Returns the total byte size of all files in the cache folder.
     * Returns 0 if the folder does not exist or is empty.
     *
     * @return int
     */
    public static function getCacheSize() : int
    {
        $folder = self::getCacheFolder();

        if($folder->exists())
        {
            return $folder->getSize();
        }

        return 0;
    }
}
