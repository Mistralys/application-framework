<?php

declare(strict_types=1);

class DBHelper_BaseFilterCriteria_Record
{
    /**
     * @var array<string,string>
     */
    private array $data;
    private DBHelper_BaseRecord $record;

    public function __construct(array $data, DBHelper_BaseRecord $record)
    {
        $this->data = $data;
        $this->record = $record;
    }

    public function getRecord() : DBHelper_BaseRecord
    {
        return $this->record;
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
