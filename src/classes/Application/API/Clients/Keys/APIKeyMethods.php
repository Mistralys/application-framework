<?php
/**
 * @package API
 * @subpackage API Keys
 */

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use Application\API\APIManager;
use DBHelper;

/**
 * Handles the names of API methods granted to an API key.
 * This can be accessed via {@see APIKeyRecord::getMethods()}.
 *
 * @package API
 * @subpackage API Keys
 */
class APIKeyMethods
{
    public const string TABLE_NAME = 'api_key_methods';
    public const string COL_API_KEY_ID = 'api_key_id';
    public const string COL_API_CLIENT_ID = 'api_client_id';
    public const string COL_METHOD_NAME = 'method_name';

    protected APIKeyRecord $key;

    public function __construct(APIKeyRecord $key)
    {
        $this->key = $key;
    }

    /**
     * Grants all available methods to the API key.
     *
     * > NOTE: This will clear any individually granted methods
     * > from the database.
     *
     * @return $this
     */
    public function grantAll() : self
    {
        $this->key->setGrantAll(true);
        $this->key->save();

        return $this->clearMethods();
    }

    /**
     * @return $this
     */
    private function clearMethods() : self
    {
        DBHelper::deleteRecords(
            self::TABLE_NAME,
            array(
                self::COL_API_KEY_ID => $this->key->getID()
            )
        );

        return $this->resetCache();
    }

    /**
     * Whether all methods are granted to the API key.
     * This differs from manually setting all methods
     * in that it will automatically include any new methods
     * added to the API in the future.
     *
     * @return bool
     */
    public function areAllGranted() : bool
    {
        return $this->key->areAllMethodsGranted();
    }

    public function getMethodNames() : array
    {
        if ($this->areAllGranted()) {
            return $this->getAvailableMethods();
        }

        return $this->loadMethods();
    }

    private static ?array $availableMethods = null;

    /**
     * Gets a list of all method names available in the system.
     * @return string[]
     */
    public function getAvailableMethods() : array
    {
        if(!isset(self::$availableMethods)) {
            self::$availableMethods = APIManager::getInstance()->getMethodIndex()->getMethodNames();
        }

        return self::$availableMethods;
    }

    /**
     * @param string[] $methodNames
     * @return $this
     */
    public function addMethods(array $methodNames) : self
    {
        foreach($methodNames as $methodName) {
            $this->addMethod($methodName);
        }

        return $this;
    }

    public function addMethod(string $methodName) : self
    {
        $this->requireValidMethodName($methodName);

        if($this->hasMethod($methodName)) {
            return $this;
        }

        DBHelper::insertDynamic(
            self::TABLE_NAME,
            array(
                self::COL_API_KEY_ID => $this->key->getID(),
                self::COL_API_CLIENT_ID => $this->key->getClientID(),
                self::COL_METHOD_NAME => $methodName,
            )
        );

        if(isset($this->loadedMethods)) {
            $this->loadedMethods[] = $methodName;
            sort($this->loadedMethods);
        }

        return $this;
    }

    public function setMethods(array $methodNames) : self
    {
        $this->clearMethods();

        $this->addMethods($methodNames);

        return $this;
    }

    public function removeMethods(array $methodNames) : self
    {
        foreach($methodNames as $methodName) {
            $this->removeMethod($methodName);
        }

        return $this;
    }

    public function removeMethod(string $methodName) : self
    {
        $this->requireValidMethodName($methodName);

        DBHelper::deleteRecords(
            self::TABLE_NAME,
            array(
                self::COL_API_KEY_ID => $this->key->getID(),
                self::COL_METHOD_NAME => $methodName
            )
        );

        if(isset($this->loadedMethods)) {
            $this->loadedMethods = array_filter(
                $this->loadedMethods,
                static function ($loadedMethodName) use ($methodName) {
                    return $loadedMethodName !== $methodName;
                }
            );
        }

        return $this;
    }

    private function requireValidMethodName(string $methodName) : void
    {
        if(in_array($methodName, $this->getAvailableMethods(), true)) {
            return;
        }

        throw new APIKeyException(
            sprintf('Method name "%s" is not valid.', $methodName)
        );
    }

    /**
     * Resets the internal cache of loaded methods.
     * @return $this
     */
    private function resetCache() : self
    {
        $this->loadedMethods = null;
        return $this;
    }

    /**
     * @var string[]|null
     */
    private ?array $loadedMethods = null;

    /**
     * Loads the list of granted methods from the database.
     *
     * > NOTE: Filters the list to only include currently
     * > available methods, as the API may have changed since
     * > the methods were granted.
     *
     * @return string[]
     */
    private function loadMethods() : array
    {
        if(isset($this->loadedMethods)) {
            return $this->loadedMethods;
        }

        $availableMethods = $this->getAvailableMethods();

        $this->loadedMethods = array_filter(
            $this->fetchMethodNames(),
            static function ($methodName) use ($availableMethods) {
                return in_array($methodName, $availableMethods, true);
            }
        );

        sort($this->loadedMethods);

        return $this->loadedMethods;
    }

    /**
     * Fetches the granted methods from the database.
     * @return string[]
     */
    private function fetchMethodNames() : array
    {
        return DBHelper::createFetchMany(self::TABLE_NAME)
            ->whereValue(self::COL_API_KEY_ID, $this->key->getID())
            ->fetchColumn(self::COL_METHOD_NAME);
    }

    public function hasMethod(string $methodName) : bool
    {
        return in_array($methodName, $this->getMethodNames(), true);
    }

    public function countMethods() : int
    {
        return count($this->getMethodNames());
    }
}
