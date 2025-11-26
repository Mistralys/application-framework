<?php

declare(strict_types=1);

namespace Application\Renamer\Index;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;
use Application\Renamer\DataColumnInterface;

class RenamerFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const string FILTER_COLUMNS = 'columns';

    protected function init(): void
    {
        $this->addGroupBy(RenamerIndex::COL_HASH);
        $this->addGroupBy(RenamerIndex::PRIMARY_NAME);
    }

    public function selectColumn(DataColumnInterface $column) : self
    {
        return $this->selectCriteriaValue(self::FILTER_COLUMNS, $column->getID());
    }

    protected function prepareQuery(): void
    {
        $this->addWhereColumnIN(RenamerIndex::COL_COLUMN_ID, $this->getCriteriaValues(self::FILTER_COLUMNS));
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}

