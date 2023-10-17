<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;
use NewsCentral\NewsEntryType;

class NewsFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const FILTER_TYPES = 'types';

    public function selectType(NewsEntryType $type) : self
    {
        return $this->selectCriteriaValue(self::FILTER_TYPES, $type->getID());
    }

    protected function prepareQuery(): void
    {
        $this->addWhereColumnIN(NewsCollection::COL_NEWS_TYPE, $this->getCriteriaValues(self::FILTER_TYPES));
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
