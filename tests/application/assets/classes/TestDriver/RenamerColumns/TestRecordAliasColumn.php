<?php

declare(strict_types=1);

namespace TestDriver\RenamerColumns;

use Application\Renamer\BaseDataColumn;
use DBHelper_FetchMany;
use TestDriver\TestDBRecords\TestDBCollection;

class TestRecordAliasColumn extends BaseDataColumn
{
    public const string COLUMN_ID = 'TestRecordAlias';

    public function getID(): string
    {
        return self::COLUMN_ID;
    }

    public function getLabel(): string
    {
        return t('Test Record Alias');
    }

    public function getTableName(): string
    {
        return TestDBCollection::TABLE_NAME;
    }

    public function getColumnName(): string
    {
        return TestDBCollection::COL_ALIAS;
    }

    public function getPrimaryColumns(): array
    {
        return array(
            TestDBCollection::PRIMARY_NAME
        );
    }
}
