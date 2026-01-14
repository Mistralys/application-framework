<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans;

use DateTime;
use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method TimeSpanRecord[] getItemsObjects()
 */
class TimeSpanFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const string FILTER_DATE = 'date';

    /**
     * Selects all time spans that intersect with the given date.
     * @param DateTime $date
     * @return $this
     */
    public function selectDate(DateTime $date) : self
    {
        return $this->selectCriteriaValue(self::FILTER_DATE, $date->format('Y-m-d'));
    }

    protected function prepareQuery(): void
    {
        foreach($this->getCriteriaValues(self::FILTER_DATE) as $date) {
            $this->addWhereStatement(
                sprintf(
                    "{span_date_start} <= '%1\$s' AND {span_date_end} >= '%1\$s'",
                    $date
                )
            );
        }
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
        self::getStatementValues($container);
    }

    public static function getStatementValues(?DBHelper_StatementBuilder_ValuesContainer $container=null) : DBHelper_StatementBuilder_ValuesContainer
    {
        return statementValues($container)
            ->table('{table_spans}', TimeSpanCollection::TABLE_NAME)
            ->table('{span_primary}', TimeSpanCollection::PRIMARY_NAME)
            ->field('{span_type}', TimeSpanRecord::COL_TYPE)
            ->field('{span_date_start}', TimeSpanRecord::COL_DATE_START)
            ->field('{span_date_end}', TimeSpanRecord::COL_DATE_END)
            ->field('{span_comments}', TimeSpanRecord::COL_COMMENTS);
    }
}
