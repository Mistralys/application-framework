<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\Microtime;

class DBHelper_DataTable implements Application_Interfaces_Loggable, Application_Interfaces_Eventable
{
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;

    const EVENT_KEYS_SAVED = 'KeysSaved';

    /**
     * @var array<string,string>
     */
    protected $valueCache = array();

    /**
     * @var string[]
     */
    protected $modifiedKeys = array();

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $primaryName;

    /**
     * @var int
     */
    private $primaryValue;

    /**
     * @var string
     */
    private $logPrefix;

    /**
     * @var string
     */
    private $logIdentifier;

    /**
     * @var bool
     */
    private $autoSave = false;

    public function __construct(string $tableName, string $primaryName, int $primaryValue, string $logPrefix)
    {
        $this->tableName = $tableName;
        $this->primaryName = $primaryName;
        $this->primaryValue = $primaryValue;
        $this->logIdentifier = sprintf('DataTable [%s]', $this->tableName);

        if(!empty($logPrefix)) {
            $this->logIdentifier = $logPrefix.' | '.$this->logIdentifier;
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

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function getKey(string $name) : string
    {
        if(isset($this->valueCache[$name])) {
            return $this->valueCache[$name];
        }

        $value = DBHelper::createFetchKey('value', $this->tableName)
        ->whereValue($this->primaryName, $this->primaryValue)
        ->fetchString();

        $this->valueCache[$name] = $value;

        return $value;
    }

    public function getIntKey(string $name) : int
    {
        return intval($this->getKey($name));
    }

    public function getDateTimeKey(string $name) : ?Microtime
    {
        $value = $this->getKey($name);

        if(empty($value)) {
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
        return $this->setKey($name, strval($value));
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

        if($this->valueCache[$name] === $value) {
            return false;
        }

        $this->log(sprintf('DataTable | Key [%s] | Value modified.', $name));

        if(!in_array($name, $this->modifiedKeys)) {
            $this->modifiedKeys[] = $name;
        }

        $this->valueCache[$name] = $value;

        if($this->autoSave) {
            return $this->save();
        }

        return true;
    }

    public function isModified() : bool
    {
        return !empty($this->modifiedKeys);
    }

    public function save() : bool
    {
        if(!$this->isModified()) {
            return false;
        }

        $this->log(sprintf('DataTable | Saving [%s] modified keys.', count($this->modifiedKeys)));

        DBHelper::requireTransaction(sprintf('Save data table keys in [%s]', $this->tableName));

        foreach($this->modifiedKeys as $name) {
            DBHelper::insertOrUpdate(
                $this->tableName,
                array(
                    $this->primaryName => $this->primaryValue,
                    'name' => $name,
                    'value' => $this->valueCache[$name]
                ),
                array($this->primaryName, 'name')
            );
        }

        $this->triggerEvent(
            self::EVENT_KEYS_SAVED,
            array($this, $this->modifiedKeys),
            DBHelper_DataTable_Events_KeysSaved::class
        );

        $this->modifiedKeys = array();

        $this->log('DataTable | Save complete.');

        return true;
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
}
