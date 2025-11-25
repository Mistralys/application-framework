<?php

declare(strict_types=1);

use Application\Collection\IntegerCollectionItemInterface;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use DBHelper\Interfaces\DBHelperRecordInterface;

class DBHelper_BaseFilterCriteria_Record
{
    /**
     * @var array<string,string>
     */
    private array $data;
    private IntegerCollectionItemInterface $record;

    public function __construct(array $data, IntegerCollectionItemInterface $record)
    {
        $this->data = $data;
        $this->record = $record;
    }

    /**
     * Returns the underlying record object, in the minimum
     * form of an {@see IntegerCollectionItemInterface}.
     * You may want to use {@see self::getDBRecord()} instead.
     *
     * @return IntegerCollectionItemInterface
     */
    public function getRecord() : IntegerCollectionItemInterface
    {
        return $this->record;
    }

    /**
     * Assumes that the record is a {@see DBHelperRecordInterface},
     * and returns it. Throws an exception otherwise.
     *
     * > NOTE: Except in rare cases where a DB collection has been
     * > mocked, the records are of this type. You may safely use
     * > this method unless otherwise documented.
     *
     * @return DBHelperRecordInterface
     * @throws BaseClassHelperException
     */
    public function getDBRecord() : DBHelperRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            DBHelperRecordInterface::class,
            $this->record
        );
    }

    public function getID() : int
    {
        return $this->record->getID();
    }

    public function hasColumn(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    public function getColumns() : array
    {
        return $this->data;
    }

    public function getColumn(string $name) : string
    {
        if(isset($this->data[$name]))
        {
            return (string)$this->data[$name];
        }

        return '';
    }

    public function getColumnInt(string $name) : int
    {
        return (int)$this->getColumn($name);
    }

    public function getColumnDate(string $name) : ?DateTime
    {
        $date = $this->getColumn($name);

        if(!empty($date))
        {
            return new DateTime($date);
        }

        return null;
    }
}
