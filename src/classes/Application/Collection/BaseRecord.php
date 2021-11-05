<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;

abstract class Application_Collection_BaseRecord implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_COULD_NOT_LOAD_DATA = 85701;

    /**
     * @var array<string,mixed>
     */
    protected $recordData;

    /**
     * @var string[]
     */
    protected $modifiedKeys = array();

    /**
     * Intentionally typeless, to allow extending classes to specify a type.
     * @return mixed
     */
    abstract public function getID();

    /**
     * A label for the type of record, e.g. "Product", which
     * is used in log messages to identify the record.
     *
     * @return string
     */
    abstract protected function getRecordTypeLabel() : string;

    /**
     * The name of the record's primary key in the data set,
     * e.g. "product_id". Ensures that its value cannot be
     * overwritten or set.
     *
     * @return string
     */
    abstract protected function getRecordPrimaryName() : string;

    /**
     * Fetches the record's data set as an associative array.
     * @return array<string,mixed>
     */
    abstract protected function loadData() : array;

    /**
     * Called at the end of the constructor, after the data has been loaded.
     */
    abstract protected function init() : void;

    /**
     * Initializes the record by loading its data, and calling `init()` afterwards.
     *
     * @throws Application_Exception
     * @see Application_Collection_BaseRecord::ERROR_COULD_NOT_LOAD_DATA
     */
    protected function initRecord() : void
    {
        $this->recordData = $this->loadData();

        if(empty($this->recordData)) {
            throw new Application_Exception(
                'Could not load record data.',
                sprintf(
                    'Tried to load data for record [%s].',
                    $this->getID()
                ),
                self::ERROR_COULD_NOT_LOAD_DATA
            );
        }

        $this->log('Data loaded successfully.');

        $this->init();
    }

    public function isModified() : bool
    {
        return !empty($this->modifiedKeys);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    final protected function getDataKey(string $name)
    {
        if(isset($this->recordData[$name]))
        {
            return $this->recordData[$name];
        }

        return null;
    }

    final protected function getDataKeyString(string $name) : string
    {
        return strval($this->getDataKey($name));
    }

    final protected function getDataKeyInt(string $name) : int
    {
        return intval($this->getDataKey($name));
    }

    final protected function getDataKeyBool(string $name) : bool
    {
        return ConvertHelper::string2bool($this->getDataKey($name));
    }

    final protected function getDataKeyArray(string $name) : array
    {
        $value = $this->getDataKey($name);

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    final protected function getDataKeyDate(string $name) : ?DateTime
    {
        $value = $this->getDataKey($name);

        if($value instanceof DateTime) {
            return $value;
        }

        if(empty($value) || !is_string($value)) {
            return null;
        }

        try
        {
            return new DateTime($value);
        }
        catch (Exception $e)
        {
            return null;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    final protected function setDataKey(string $name, $value) : bool
    {
        // Avoid being able to change the record's primary
        // in the data set.
        if($name === $this->getRecordPrimaryName()) {
            return false;
        }

        $old = $this->getDataKey($name);

        if(ConvertHelper::areVariablesEqual($old, $value)) {
            return false;
        }

        $this->log(sprintf('DataKey [%s] | Value modified.', $name));

        $this->recordData[$name] = $value;

        if(!in_array($name, $this->modifiedKeys)) {
            $this->modifiedKeys[] = $name;
        }

        return true;
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            '%s [%s]',
            $this->getRecordTypeLabel(),
            $this->getID()
        );
    }
}
