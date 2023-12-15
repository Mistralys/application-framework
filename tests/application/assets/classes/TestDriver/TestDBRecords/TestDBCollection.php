<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use DBHelper;
use DBHelper_BaseCollection;

/**
 * @method TestDBRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 * @method TestDBRecord getByID(int $record_id)
 * @method TestDBFilterCriteria getFilterCriteria()
 * @method TestDBFilterSettings getFilterSettings()
 */
class TestDBCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'test_records';
    public const TABLE_NAME_DATA = 'test_records_data';
    public const PRIMARY_NAME = 'record_id';
    public const COL_ALIAS = 'alias';
    public const COL_LABEL = 'label';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if(self::$instance === null) {
            self::$instance = DBHelper::createCollection(self::class, null, true);
        }

        return self::$instance;
    }

    public function getRecordClassName(): string
    {
        return TestDBRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return TestDBFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return TestDBFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label')
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
            self::COL_LABEL => $label,
            self::COL_ALIAS => $alias
        ));
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)
            ->makeRequired();

        $this->keys->register(self::COL_ALIAS)
            ->makeRequired();
    }
}
