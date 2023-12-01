<?php

declare(strict_types=1);

namespace TestDriver;

use DBHelper_BaseCollection;
use TestDriver\TestDBCollection\TestDBRecord;
use TestDriver_TestDBCollection_FilterCriteria;
use TestDriver_TestDBCollection_FilterSettings;

/**
 * @method TestDBRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 * @method TestDBRecord getByID(int $record_id)
 */
class TestDBCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'test_records';
    public const TABLE_NAME_DATA = 'test_records_data';
    public const PRIMARY_NAME = 'record_id';

    public function getRecordClassName(): string
    {
        return TestDBRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return TestDriver_TestDBCollection_FilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return TestDriver_TestDBCollection_FilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return TestDBRecord::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            TestDBRecord::COL_LABEL => t('Label')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return 'test_record';
    }

    public function getCollectionLabel(): string
    {
        return t('Test records');
    }

    public function getRecordLabel(): string
    {
        return t('Test record');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    public function createTestRecord(string $label, string $alias): TestDBRecord
    {
        return $this->createNewRecord(array(
            TestDBRecord::COL_LABEL => $label,
            TestDBRecord::COL_ALIAS => $alias
        ));
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(TestDBRecord::COL_LABEL)
            ->makeRequired();

        $this->keys->register(TestDBRecord::COL_ALIAS)
            ->makeRequired();
    }
}
