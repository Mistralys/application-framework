<?php

declare(strict_types=1);

namespace Application\Renamer\Index;

use DBHelper;
use DBHelper_BaseCollection;

/**
 * @method RenamerRecord[] getAll()
 * @method RenamerRecord getByID(int $record_id)
 */
class RenamerIndex extends DBHelper_BaseCollection
{
    public const string TABLE_NAME = 'renamer_index';
    public const string PRIMARY_NAME = 'index_id';
    public const string COL_COLUMN_ID = 'column_id';
    public const string COL_HASH = 'hash';
    public const string COL_PRIMARY_VALUES = 'primary_values';
    public const string RECORD_TYPE = 'renamer_index';


    public function getRecordClassName(): string
    {
        return RenamerRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return RenamerFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return RenamerFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_COLUMN_ID => t('Column ID'),
            self::COL_PRIMARY_VALUES => t('Primary Values')
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
        return self::RECORD_TYPE;
    }

    public function getCollectionLabel(): string
    {
        return t('Renamer Search Index');
    }

    public function getRecordLabel(): string
    {
        return t('Renamer index record');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    public function countAllRecords(): int
    {
        return DBHelper::fetchCount("SELECT COUNT(DISTINCT " . self::COL_HASH . ") AS `count` FROM `" . self::TABLE_NAME . "`");
    }
}
