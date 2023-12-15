<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method TestDBRecord[] getItemsObjects()
 */
class TestDBFilterCriteria extends DBHelper_BaseFilterCriteria
{
    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
