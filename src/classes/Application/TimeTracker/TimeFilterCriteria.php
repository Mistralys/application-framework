<?php

declare(strict_types=1);

namespace Application\TimeTracker;

use AppUtils\Microtime;
use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

class TimeFilterCriteria extends DBHelper_BaseFilterCriteria
{
    private ?Microtime $fixedDate = null;

    public function setFixedDate(Microtime $fixedDate) : self
    {
        $this->fixedDate = $fixedDate;
        return $this;
    }

    protected function prepareQuery(): void
    {
        if(isset($this->fixedDate)) {
            $this->addWhereColumnEquals(TimeTrackerCollection::COL_DATE, $this->fixedDate->format('Y-m-d'));
        }
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
