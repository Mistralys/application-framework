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
use Application\API\APIManager;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\FileHelper;
use AppUtils\FileHelper\JSONFile;

/**
 * API method indexing module: Creates a cache file on disk
 * that is used at runtime to look up whether a method exists,
 * and to fetch its class name without having to use the
 * {@see APIMethodCollection} to find it.
 *
 * ## Usage
 *
 * Use {@see \Application\API\APIManager::getMethodIndex()} to get an instance
 * of this class, and then call {@see methodExists()} to check
 * if a method exists, or {@see getMethodClass()} to get the
 * class name of a method.
 *
 * @package API
 * @subpackage Method Collection
 */
class APIMethodIndex implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private APIManager $api;
    private string $logIdentifier;

    public function __construct(APIManager $api)
    {
        $this->api = $api;
        $this->logIdentifier = 'API | MethodIndex';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    /**
     * @return string[]
     */
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

    /**
     * @return array<string,class-string<APIMethodInterface>>
     */
    private function getIndex() : array
    {
        if(isset($this->index)) {
            return $this->index;
        }

        $file = $this->getDataFile();

        // Build the index on demand if it doesn't exist yet.
        if(!$file->exists()) {
            $this->log('API method index not found, building it now...');
            $this->build();
        }

        $this->index = $file->getData();

        return $this->index;
    }

    public function build() : self
    {
        $methods = array();

        $this->logHeader('Building API method index...');

        foreach($this->api->getMethodCollection()->getAll() as $method)
        {
            $this->log('- Method [%s]...', $method->getMethodName());
            $methods[$method->getMethodName()] = get_class($method);

            // Access versions: This will cause methods that use
            // class-based versioning to register their versions
            // in the class loader.
            $method->getVersions();
        }

        $this->getDataFile()->putData($methods);

        $this->log(sprintf('Index saved to disk at [%s].', FileHelper::relativizePath($this->getDataFile()->getPath(), APP_ROOT)));

        return $this;
    }

    public function getDataFile() : JSONFile
    {
        return JSONFile::factory(Application::getStorageSubfolderPath('api').'/method-index.json')
            ->setEscapeSlashes(false)
            ->setTrailingNewline(true)
            ->setPrettyPrint(true);
    }

    public function getCacheLocation() : APICacheLocation
    {
        return new APICacheLocation($this);
    }
}
