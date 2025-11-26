<?php

declare(strict_types=1);

namespace application\assets\classes\TestDriver\RenamerColumns;

use Application\Renamer\BaseDataColumn;
use DBHelper_FetchMany;
use TestDriver\TestDBRecords\TestDBCollection;

class TestRecordLabelColumn extends BaseDataColumn
{
    public const string COLUMN_ID = 'TestRecordLabel';

    public function getID(): string
    {
        return self::COLUMN_ID;
    }

    public function getLabel(): string
    {
        return t('Test Record Label');
    }

    public function getTableName(): string
    {
        return TestDBCollection::TABLE_NAME;
    }

    public function getColumnName(): string
    {
        return TestDBCollection::COL_LABEL;
    }

    public function getPrimaryColumns(): array
    {
        return array(
            TestDBCollection::PRIMARY_NAME
        );
    }
}
