<?php
/**
 * @package API
 * @subpackage Method Collection
 */

declare(strict_types=1);

namespace Application\AppFactory;

use Application\API\Collection\APIMethodIndex;
use Application\CacheControl\BaseCacheLocation;
use AppUtils\FileHelper\JSONFile;

/**
 * Cache location description class for the API method index,
 * so it can be handled via the cache control system.
 *
 * @package API
 * @subpackage Method Collection
 */
class APICacheLocation extends BaseCacheLocation
{
    public const string CACHE_ID = 'APIMethodIndex';
    private JSONFile $file;

    public function __construct(APIMethodIndex $index)
    {
        $this->file = $index->getDataFile();
    }

    public function getID(): string
    {
        return self::CACHE_ID;
    }

    public function getByteSize(): int
    {
        if($this->file->exists()) {
            return $this->file->getSize();
        }

        return 0;
    }

    public function getLabel(): string
    {
        return t('API Method Index');
    }

    public function clear(): void
    {
        $this->file->delete();
    }
}
