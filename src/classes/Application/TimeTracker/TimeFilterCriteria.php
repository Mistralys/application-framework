<?php

declare(strict_types=1);

namespace Application\TimeTracker;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

class TimeFilterCriteria extends DBHelper_BaseFilterCriteria
{
    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
