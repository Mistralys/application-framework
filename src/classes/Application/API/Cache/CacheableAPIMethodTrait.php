<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\AppFactory;
use AppUtils\FileHelper\JSONFile;

/**
 * Provides default implementations for {@see CacheableAPIMethodInterface}.
 *
 * Use this trait inside an API method class that also implements
 * {@see CacheableAPIMethodInterface}. The consuming class must define:
 * - {@see CacheableAPIMethodInterface::getCacheStrategy()}
 * - {@see CacheableAPIMethodInterface::getCacheKeyParameters()}
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodInterface
 * @phpstan-require-implements CacheableAPIMethodInterface
 */
trait CacheableAPIMethodTrait
{
    /**
     * Builds a deterministic MD5 hash from the method name, API version,
     * and the sorted cache key parameters.
     *
     * @param string $version
     * @return string
     */
    public function getCacheKey(string $version) : string
    {
        $params = $this->getCacheKeyParameters();
        ksort($params);

        return md5($this->getMethodName() . '|' . $version . '|' . json_encode($params, JSON_THROW_ON_ERROR));
    }

    /**
     * Resolves the JSON cache file for the given version.
     *
     * @param string $version
     * @return JSONFile
     */
    protected function getCacheFile(string $version) : JSONFile
    {
        $folder = APICacheManager::getMethodCacheFolder($this->getMethodName());

        return JSONFile::factory($folder->getPath() . '/' . $this->getCacheKey($version) . '.json');
    }

    /**
     * Reads response data from the cache file for the given version.
     * Returns null if the cache file does not exist or is no longer valid
     * according to the configured strategy. If the cache file is corrupt
     * (parse failure), logs an error, deletes the file, and returns null.
     *
     * @param string $version
     * @return array|null
     */
    public function readFromCache(string $version) : ?array
    {
        $cacheFile = $this->getCacheFile($version);

        if(!$cacheFile->exists())
        {
            return null;
        }

        if(!$this->getCacheStrategy()->isCacheFileValid($cacheFile))
        {
            return null;
        }

        try
        {
            return $cacheFile->parse();
        }
        catch(\Throwable $e)
        {
            // Cache file is corrupt — log the event for operator observability, then
            // delete the file best-effort and signal a cache miss.
            try
            {
                AppFactory::createLogger()->logError(
                    sprintf(
                        'Corrupt API cache file detected and deleted (error code %d). Path: %s | Error: %s',
                        APICacheException::ERROR_CACHE_FILE_CORRUPT,
                        $cacheFile->getPath(),
                        $e->getMessage()
                    )
                );
            }
            catch(\Throwable $ignored) {}

            try { $cacheFile->delete(); } catch(\Throwable $ignored) {}
            return null;
        }
    }

    /**
     * Writes response data to the cache file for the given version.
     * The parent directory is created automatically if it does not exist.
     *
     * @param string $version
     * @param array $data
     * @return void
     */
    public function writeToCache(string $version, array $data) : void
    {
        $this->getCacheFile($version)->putData($data);
    }

    /**
     * Invalidates all cached entries for this API method by delegating
     * to {@see APICacheManager::invalidateMethod()}.
     *
     * @return void
     */
    public function invalidateCache() : void
    {
        APICacheManager::invalidateMethod($this->getMethodName());
    }
}
