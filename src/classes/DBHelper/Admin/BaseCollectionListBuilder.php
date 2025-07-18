<?php

declare(strict_types=1);

namespace DBHelper\Admin;

use Application\Interfaces\FilterCriteriaInterface;
use Application_FilterSettings;
use AppUtils\ConvertHelper\JSONConverter;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use DBHelper_Exception;
use UI\DataGrid\BaseListBuilder;

abstract class BaseCollectionListBuilder extends BaseListBuilder
{
    protected function init(): void
    {
        $this->setListID($this->getCollection()->getRecordTypeName().'-list');
    }

    abstract public function getCollection() : DBHelper_BaseCollection;

    protected function createFilterCriteria(): FilterCriteriaInterface
    {
        return $this->getCollection()->getFilterCriteria();
    }

    protected function createFilterSettings(): Application_FilterSettings
    {
        return $this->getCollection()->getFilterSettings();
    }

    public function getRecordTypeLabelSingular(): string
    {
        return $this->getCollection()->getRecordLabel();
    }

    /**
     * @param array<string,string> $itemData
     * @return DBHelper_BaseRecord
     * @throws DBHelper_Exception
     */
    protected function resolveRecord(array $itemData): object
    {
        $collection = $this->getCollection();
        $primaryName = $collection->getParentPrimaryName();

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