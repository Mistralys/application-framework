<?php

declare(strict_types=1);

namespace DBHelper\Admin;

use Application\Interfaces\FilterCriteriaInterface;
use AppUtils\ConvertHelper\JSONConverter;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\DBHelperFilterSettingsInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_Exception;
use UI\DataGrid\BaseListBuilder;

abstract class BaseCollectionListBuilder extends BaseListBuilder
{
    protected function init(): void
    {
        $this->setListID($this->getCollection()->getRecordTypeName().'-list');
    }

    abstract public function getCollection() : DBHelperCollectionInterface;

    protected function createFilterCriteria(): FilterCriteriaInterface
    {
        return $this->getCollection()->getFilterCriteria();
    }

    protected function createFilterSettings(): DBHelperFilterSettingsInterface
    {
        return $this->getCollection()->getFilterSettings();
    }

    public function getRecordTypeLabelSingular(): string
    {
        return $this->getCollection()->getRecordLabel();
    }

    /**
     * @param array<string,string> $itemData
     * @return DBHelperRecordInterface
     * @throws DBHelper_Exception
     */
    protected function resolveRecord(array $itemData): DBHelperRecordInterface
    {
        $collection = $this->getCollection();
        $primaryName = $collection->getRecordPrimaryName();

        if(isset($itemData[$primaryName])) {
            return $collection->getByID((int)$itemData[$primaryName]);
        }

        throw new DBHelper_Exception(
            'Record not found by list builder data set.',
            sprintf(
                'Primary name: [%s] '.PHP_EOL.
                'Data: '.PHP_EOL.
                '%s',
                $primaryName,
                JSONConverter::var2jsonSilent($itemData, JSON_PRETTY_PRINT)
            )
        );
    }
}
