<?php
/**
 * File containing the class {@see DBHelper_DataTable}.
 *
 * @package DBHelper
 * @subpackage DataTables
 * @see DBHelper_DataTable
 */

declare(strict_types=1);

use Application\Application;
use AppUtils\ConvertHelper;
use AppUtils\Microtime;

/**
 * Data table handler for records: Handles fetching
 * data keys for a record from a DB table that contains
 * name => value pair data records.
 *
 * The data table must have at minimum three columns:
 *
 * 1. The record ID
 * 2. The data key name (default `name`*)
 * 3. The data key value (default `value`*)
 *
 * * The defaults can be changed.
 *
 * **Name column size limitation**
 *
 * Make sure you set the maximum key name length to match
 * the size of the column in the database to avoid problems:
 * The class will automatically replace key names that are
 * longer with an MD5 hash (internally only).
 *
 *   > Note: Because of the MD5 conversion, the minimum size
 *   > for the name column is 32 characters.
 *
 * @package DBHelper
 * @subpackage DataTables
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class DBHelper_DataTable implements Application_Interfaces_Loggable, Application_Interfaces_Eventable
{
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;

    public const EVENT_KEYS_SAVED = 'KeysSaved';
    public const EVENT_KEYS_DELETED = 'KeysDeleted';

    public const ERROR_INVALID_MAX_KEY_NAME_LENGTH = 97301;

    /**
     * The max key length may not be smaller than this.
     * @see DBHelper_DataTable::setMaxKeyNameLength()
     */
    public const MIN_MAX_KEY_NAME_LENGTH = 32;

    /**
     * @var array<string,string>
     */
    protected array $valueCache = array();

    /**
     * @var string[]
     */
    protected array $modifiedKeys = array();

    private string $tableName;

    private string $primaryName;

    private int $primaryValue;

    private string $logIdentifier;

    private bool $autoSave = false;

    /**
     * @var string[]
     */
    protected array $deletedKeys = array();

    private string $keyValue = 'value';

    private string $keyName = 'name';

    private int $maxKeyNameLength = 250;

    public function __construct(string $tableName, string $primaryName, int $primaryValue, string $logPrefix)
    {
        $this->tableName = $tableName;
        $this->primaryName = $primaryName;
        $this->primaryValue = $primaryValue;
        $this->logIdentifier = sprintf('DataTable [%s]', $this->tableName);

        if (!empty($logPrefix))
        {
            $this->logIdentifier = $logPrefix . ' | ' . $this->logIdentifier;
        }
    }

    /**
     * Enables or disables auto saving. When enabled, data keys
     * will be saved to the database each time they are modified.
     * Otherwise, the `save()` method must be called manually.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setAutoSave(bool $enabled) : DBHelper_DataTable
    {
        $this->autoSave = $enabled;
        return $this;
    }

    public function getLogIdentifier() : string
    {
        return $this->logIdentifier;
    }

    public function getKey(string $name) : string
    {
        if (in_array($name, $this->deletedKeys, true) !== false)
        {
            $this->valueCache[$name] = '';
        }

        if (isset($this->valueCache[$name]))
        {
            return $this->valueCache[$name];
        }

        $value = DBHelper::createFetchKey($this->keyValue, $this->tableName)
            ->whereValue($this->primaryName, $this->primaryValue)
            ->whereValue($this->keyName, $name)
            ->fetchString();

        $this->valueCache[$name] = $value;

        return $value;
    }

    public function resetCache() : DBHelper_DataTable
    {
        $this->valueCache = array();

        return $this;
    }

    public function isKeyExists(string $name) : bool
    {
        if (isset($this->valueCache[$name]))
        {
            return true;
        }

        return DBHelper::createFetchKey($this->keyValue, $this->tableName)
            ->whereValues(array($this->primaryName => $this->primaryValue, $this->keyName => $name))->exists();
    }

    public function getIntKey(string $name) : int
    {
        return (int)$this->getKey($name);
    }

    /**
     * @param string $name
     * @return Application_User|null
     * @throws Application_Exception
     */
    public function getUserKey(string $name) : ?Application_User
    {
        $id = $this->getIntKey($name);
        if($id !== 0 && Application::userIDExists($id)) {
            return Application::createUser($id);
        }

        return null;
    }

    public function getDateTimeKey(string $name) : ?Microtime
    {
        $value = $this->getKey($name);

        if (empty($value))
        {
            return null;
        }

        try
        {
            return new Microtime($value);
        }
        catch (Exception $e)
        {
            return null;
        }
    }

    public function getBoolKey(string $name) : bool
    {
        return ConvertHelper::string2bool($this->getKey($name));
    }

    public function setIntKey(string $name, int $value) : bool
    {
        return $this->setKey($name, (string)$value);
    }

    public function setUserKey(string $name, Application_User $user) : bool
    {
        return $this->setIntKey($name, $user->getID());
    }

    public function setBoolKey(string $name, bool $value) : bool
    {
        return $this->setKey($name, ConvertHelper::bool2string($value));
    }

    public function setDateTimeKey(string $name, Microtime $value) : bool
    {
        return $this->setKey($name, $value->getMySQLDate());
    }

    public function setKey(string $name, string $value) : bool
    {
        $this->getKey($name);

        if (($key = array_search($name, $this->deletedKeys, true)) !== false)
        {
            unset($this->deletedKeys[$key]);
        }

        if ($this->valueCache[$name] === $value)
        {
            return false;
        }

        $this->log(sprintf('DataTable | Key [%s] | Value modified.', $name));

        if (!in_array($name, $this->modifiedKeys, true))
        {
            $this->modifiedKeys[] = $name;
        }

        $this->valueCache[$name] = $value;

        if ($this->autoSave)
        {
            return $this->save();
        }

        return true;
    }

    public function deleteKey(string $name) : bool
    {
        if (!$this->isKeyExists($name))
        {
            return false;
        }

        $this->log(sprintf('DataTable | Key [%s] | deleted.', $name));

        if (!in_array($name, $this->deletedKeys, true))
        {
            $this->deletedKeys[] = $name;
        }

        if (($key = array_search($name, $this->modifiedKeys, true)) !== false)
        {
            unset($this->modifiedKeys[$key]);
        }

        unset($this->valueCache[$name]);

        if ($this->autoSave)
        {
            return $this->save();
        }

        return true;
    }

    public function hasModifiedKeys() : bool
    {
        return !empty($this->modifiedKeys);
    }

    public function hasDeletedKeys() : bool
    {
        return !empty($this->deletedKeys);
    }

    public function save() : bool
    {
        if (!$this->hasModifiedKeys() && !$this->hasDeletedKeys())
        {
            return false;
        }

        $this->saveModifiedKeys();
        $this->saveDeletedKeys();

        $this->log('DataTable | Save complete.');

        return true;
    }

    private function saveModifiedKeys() : void
    {
        if (!$this->hasModifiedKeys())
        {
            return;
        }

        $this->log(sprintf('DataTable | Saving [%s] modified keys.', count($this->modifiedKeys)));

        DBHelper::requireTransaction(sprintf('Save data table keys in [%s]', $this->tableName));

        foreach ($this->modifiedKeys as $name)
        {
            $storageName = $this->getStorageKeyName($name);

            DBHelper::insertOrUpdate(
                $this->tableName,
                array(
                    $this->primaryName => $this->primaryValue,
                    $this->keyName => $storageName,
                    $this->keyValue => $this->valueCache[$name]
                ),
                array($this->primaryName, $this->keyName)
            );
        }

        $this->triggerEvent(
            self::EVENT_KEYS_SAVED,
            array($this, $this->modifiedKeys),
            DBHelper_DataTable_Events_KeysSaved::class
        );

        $this->modifiedKeys = array();
    }

    private function saveDeletedKeys() : void
    {
        if (!$this->hasDeletedKeys())
        {
            return;
        }

        $this->log(sprintf('DataTable | Deleting [%s] keys.', count($this->deletedKeys)));

        DBHelper::requireTransaction(sprintf('Delete data table keys in [%s]', $this->tableName));

        foreach ($this->deletedKeys as $name)
        {
            $storageName = $this->getStorageKeyName($name);

            DBHelper::deleteRecords(
                $this->tableName,
                array(
                    $this->primaryName => $this->primaryValue,
                    $this->keyName => $storageName
                )
            );
        }

        $this->triggerEvent(
            self::EVENT_KEYS_DELETED,
            array($this, $this->deletedKeys),
            DBHelper_DataTable_Events_KeysDeleted::class
        );

        $this->deletedKeys = array();
    }

    public function getStorageKeyName(string $name) : string
    {
        if(strlen($name) > $this->maxKeyNameLength)
        {
            return md5($name);
        }

        return $name;
    }

    /**
     * Adds a listener for the `KeysSaved` event.
     *
     * Listener arguments:
     *
     * 1. `DBHelper_DataTable` The data table instance
     * 2. `string[]` The names of the keys that were saved
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function addKeysSavedListener(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_KEYS_SAVED, $callback);
    }

    /**
     * Adds a listener for the `KeysDeleted` event.
     *
     * Listener arguments:
     *
     * 1. `DBHelper_DataTable` The data table instance
     * 2. `string[]` The names of the keys that were deleted
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function addKeysDeletedListener(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_KEYS_DELETED, $callback);
    }

    public function isAutoSaveEnabled() : bool
    {
        return $this->autoSave;
    }

    /**
     * Sets the name of the database column used to store
     * the record names.
     *
     * @param string $name
     * @return $this
     */
    public function setNameColumnName(string $name) : DBHelper_DataTable
    {
        $this->keyName = $name;
        return $this;
    }

    /**
     * Sets the name of the database column used to store
     * the record values.
     *
     * @param string $name
     * @return $this
     */
    public function setValueColumnName(string $name) : DBHelper_DataTable
    {
        $this->keyValue = $name;
        return $this;
    }

    /**
     * @param int $length
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_DataTable::ERROR_INVALID_MAX_KEY_NAME_LENGTH
     */
    public function setMaxKeyNameLength(int $length) : DBHelper_DataTable
    {
        $min = self::MIN_MAX_KEY_NAME_LENGTH;

        if($length < $min)
        {
            throw new DBHelper_Exception(
                'Maximum key name length too small.',
                sprintf(
                    'Cannot use [%s] as  maximum key length. The minimum value is [%s].',
                    $length,
                    $min
                ),
                self::ERROR_INVALID_MAX_KEY_NAME_LENGTH
            );
        }

        $this->maxKeyNameLength = $length;
        return $this;
    }
}
