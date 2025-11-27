<?php

declare(strict_types=1);

namespace Application\Renamer\Index;

use Application\Interfaces\FilterCriteriaInterface;
use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;
use Application\Renamer\DataColumnInterface;

class RenamerFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const string FILTER_COLUMNS = 'columns';

    protected function init(): void
    {
        $this->addGroupBy(RenamerIndex::COL_HASH);

        $this->setOrderBy(RenamerIndex::PRIMARY_NAME);
    }

    protected function getSelect(): array
    {
        // Select the hash and the minimum primary name for grouping
        // to have a consistent representative for each group, and
        // keep the database happy even with `FULL_GROUP_BY` enabled.
        return array(
            RenamerIndex::COL_HASH,
            'MIN('.RenamerIndex::PRIMARY_NAME.') AS '.RenamerIndex::PRIMARY_NAME
        );
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

