<?php

declare(strict_types=1);

namespace Maileditor\Renamer;

use Application\Renamer\DataColumnInterface;
use Application\Renamer\RenamingManager;
use AppUtils\ArrayDataCollection;
use AppUtils\Microtime;

class RenamerConfig
{
    public const string KEY_SEARCH = 'search';
    public const string KEY_COLUMN_IDS = 'column_ids';
    public const string KEY_DATE = 'date';
    public const string KEY_CASE_SENSITIVE = 'case_sensitive';

    private ArrayDataCollection $data;

    public function __construct(ArrayDataCollection $data)
    {
        $this->data = $data;
    }

    public function getDate() : Microtime
    {
        return $this->data->getMicrotime(self::KEY_DATE) ?? Microtime::createNow();
    }

    public function getColumnIDs() : array
    {
        return $this->data->getJSONArray(self::KEY_COLUMN_IDS);
    }

    public function isCaseSensitive() : bool
    {
        return $this->data->getBool(self::KEY_CASE_SENSITIVE);
    }

    /**
     * @return DataColumnInterface[]
     */
    public function getColumns() :  array
    {
        $result = array();
        $collection = RenamingManager::getInstance()->getColumns();

        foreach($this->getColumnIDs() as $columnID) {
            $result[] = $collection->getByID($columnID);
        }

        return $result;
    }

    public function getSearch() : string
    {
        return $this->data->getString(self::KEY_SEARCH);
    }
}
