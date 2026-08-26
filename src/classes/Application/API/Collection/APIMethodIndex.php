<?php
/**
 * @package API
 * @subpackage Method Collection
 */

declare(strict_types=1);

namespace Application\API\Collection;

use Application\API\APIException;
use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\AppFactory\APICacheLocation;
use Application\Application;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\FileHelper;
use AppUtils\FileHelper\JSONFile;

/**
 * API method indexing module: Creates a versioned cache file on
 * disk that is used at runtime to look up whether a method exists,
 * fetch its class name, and read its declared right and group ID
 * without having to use the {@see APIMethodCollection}.
 *
 * ## Usage
 *
 * Use {@see APIManager::getMethodIndex} to get an instance
 * of this class, and then call {@see methodExists()} to check
 * if a method exists, {@see getMethodClass()} to get the
 * class name, or {@see getEntry()} for the full typed entry.
 *
 * @package API
 * @subpackage Method Collection
 */
class APIMethodIndex implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const int SCHEMA_VERSION = 2;
    public const string KEY_SCHEMA_VERSION = 'schema_version';
    public const string KEY_METHODS = 'methods';

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
     * @param string $methodName
     * @return class-string<APIMethodInterface>
     * @throws APIException
     */
    public function getMethodClass(string $methodName) : string
    {
        return $this->getEntry($methodName)->getClassName();
    }

    /**
     * @throws APIException {@see APIException::ERROR_METHOD_NOT_IN_INDEX}
     */
    public function getEntry(string $methodName) : APIMethodIndexEntry
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
     * @var array<string,APIMethodIndexEntry>|null
     */
    private ?array $index = null;

    /**
     * Nulls the in-memory index so the next {@see getIndex()} call
     * re-reads the data file from disk.
     */
    public function clearIndexCache() : self
    {
        $this->index = null;
        return $this;
    }

    /**
     * @return array<string,APIMethodIndexEntry>
     * @throws APIException {@see APIException::ERROR_INDEX_SCHEMA_VERSION_MISMATCH}
     */
    private function getIndex() : array
    {
        if(isset($this->index)) {
            return $this->index;
        }

        $file = $this->getDataFile();

        if(!$file->exists()) {
            $this->log('API method index not found, building it now...');
            $this->build();
        }

        $data = $file->getData();

        if(($data[self::KEY_SCHEMA_VERSION] ?? null) !== self::SCHEMA_VERSION) {
            $this->log(
                'Schema version mismatch (expected %d, got %s), rebuilding...',
                self::SCHEMA_VERSION,
                (string)($data[self::KEY_SCHEMA_VERSION] ?? 'absent')
            );

            $this->build();

            $data = $this->getDataFile()->getData();

            if(($data[self::KEY_SCHEMA_VERSION] ?? null) !== self::SCHEMA_VERSION) {
                throw new APIException(
                    'Method index schema version mismatch after rebuild',
                    sprintf(
                        'Expected schema version [%d] but got [%s] after a rebuild.',
                        self::SCHEMA_VERSION,
                        (string)($data[self::KEY_SCHEMA_VERSION] ?? 'absent')
                    ),
                    APIException::ERROR_INDEX_SCHEMA_VERSION_MISMATCH
                );
            }
        }

        $this->index = $this->hydrateIndex($data);

        return $this->index;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,APIMethodIndexEntry>
     */
    private function hydrateIndex(array $data) : array
    {
        $entries = array();

        foreach(($data[self::KEY_METHODS] ?? array()) as $methodName => $entryData) {
            $entries[$methodName] = APIMethodIndexEntry::fromArray($entryData);
        }

        return $entries;
    }

    /**
     * @throws APIException {@see APIException::ERROR_UNKNOWN_DECLARED_RIGHT}
     */
    public function build() : self
    {
        $methods = array();
        $unknownRights = array();

        $this->logHeader('Building API method index...');

        $rightsManager = Application::createSystemUser()->getRightsManager();

        foreach($this->api->getMethodCollection()->getAll() as $method)
        {
            $this->log('- Method [%s]...', $method->getMethodName());

            $declaredRight = null;

            if($method instanceof APIKeyMethodInterface) {
                $declaredRight = $method->getRequiredRight();
            }

            $entry = new APIMethodIndexEntry(
                $method->getMethodName(),
                get_class($method),
                $declaredRight,
                $method->getGroup()->getID()
            );

            $methods[$method->getMethodName()] = $entry->toArray();

            if($declaredRight !== null && !$rightsManager->rightIDExists($declaredRight)) {
                $unknownRights[] = sprintf('%s (right: %s)', $method->getMethodName(), $declaredRight);
            }

            // Access versions: This will cause methods that use
            // class-based versioning to register their versions
            // in the class loader.
            $method->getVersions();
        }

        if(!empty($unknownRights)) {
            throw new APIException(
                'Unknown declared rights in API methods',
                sprintf(
                    'The following API methods declare rights that are not registered: '.PHP_EOL.
                    '- %s',
                    implode(PHP_EOL.'- ', $unknownRights)
                ),
                APIException::ERROR_UNKNOWN_DECLARED_RIGHT
            );
        }

        $document = array(
            self::KEY_SCHEMA_VERSION => self::SCHEMA_VERSION,
            self::KEY_METHODS => $methods,
        );

        $this->getDataFile()->putData($document);

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
