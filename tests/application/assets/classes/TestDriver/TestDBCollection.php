<?php

declare(strict_types=1);

/**
 * @method TestDriver_TestDBCollection_TestDBRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class TestDriver_TestDBCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'test_records';
    public const TABLE_NAME_DATA = 'test_records_data';
    public const PRIMARY_NAME = 'record_id';

    public function getRecordClassName(): string
    {
        return TestDriver_TestDBCollection_TestDBRecord::class;
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
        return TestDriver_TestDBCollection_TestDBRecord::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            TestDriver_TestDBCollection_TestDBRecord::COL_LABEL => t('Label')
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

    public function createTestRecord(string $label, string $alias): TestDriver_TestDBCollection_TestDBRecord
    {
        return $this->createNewRecord(array(
            TestDriver_TestDBCollection_TestDBRecord::COL_LABEL => $label,
            TestDriver_TestDBCollection_TestDBRecord::COL_ALIAS => $alias
        ));
    }
}
