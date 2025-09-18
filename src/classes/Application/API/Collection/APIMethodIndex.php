<?php
/**
 * @package API
 * @subpackage Method Collection
 */

declare(strict_types=1);

namespace Application\API\Collection;

use Application;
use Application\API\APIException;
use Application\API\APIMethodInterface;
use Application\AppFactory\APICacheLocation;
use Application_API;
use AppUtils\FileHelper\JSONFile;

/**
 * API method indexing module: Creates a cache file on disk
 * that is used at runtime to look up whether a method exists,
 * and to fetch its class name without having to use the
 * {@see APIMethodCollection} to find it.
 *
 * ## Usage
 *
 * Use {@see Application_API::getMethodIndex()} to get an instance
 * of this class, and then call {@see methodExists()} to check
 * if a method exists, or {@see getMethodClass()} to get the
 * class name of a method.
 *
 * @package API
 * @subpackage Method Collection
 */
class APIMethodIndex
{
    private Application_API $api;

    public function __construct(Application_API $api)
    {
        $this->api = $api;
    }

    public function getMethodNames() : array
    {
        return array_keys($this->getIndex());
    }

    public function methodExists(string $methodName) : bool
    {
        $index = $this->getIndex();
        return isset($index[$methodName]);
    }

    /**
     * @param class-string<Application\API\APIMethodInterface> $methodName
     * @return string
     * @throws APIException
     */
    public function getMethodClass(string $methodName) : string
    {
        $index = $this->getIndex();

        if(isset($index[$methodName])) {
            return $index[$methodName];
        }

        throw new APIException(
            'Unknown API method',
            sprintf(
                'The API method [%s] is not known in the index. '.PHP_EOL.
                'The index may be outdated, or the method truly does not exist. '.PHP_EOL.
                'These are all known methods in the index: '.PHP_EOL.
                '(!= methods on disk if the index is outdated) '.PHP_EOL.
                PHP_EOL.
                '- %s',
                $methodName,
                implode(PHP_EOL.'- ', array_keys($index))
            ),
            APIException::ERROR_METHOD_NOT_IN_INDEX
        );
    }

    /**
     * @var array<string,class-string<APIMethodInterface>>|null
     */
    private ?array $index = null;

    private function getIndex() : array
    {
        if(isset($this->index)) {
            return $this->index;
        }

        $file = $this->getDataFile();

        // Build the index on demand if it doesn't exist yet.
        if(!$file->exists()) {
            $this->build();
        }

        $this->index = $file->getData();

        return $this->index;
    }

    public function build() : self
    {
        $methods = array();

        foreach($this->api->getMethodCollection()->getAll() as $method) {
            $methods[$method->getMethodName()] = get_class($method);
        }

        $this->getDataFile()->putData($methods);

        return $this;
    }

    public function getDataFile() : JSONFile
    {
        return JSONFile::factory(Application::getStorageSubfolderPath('api').'/method-index.ser')
            ->setEscapeSlashes(false)
            ->setTrailingNewline(true)
            ->setPrettyPrint(true);
    }

    public function getCacheLocation() : APICacheLocation
    {
        return new APICacheLocation($this);
    }
}
