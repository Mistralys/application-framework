<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;
use NewsCentral\NewsEntryStatus;
use NewsCentral\NewsEntryType;

/**
 * @method NewsEntry[] getItemsObjects()
 */
class NewsFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const FILTER_TYPES = 'types';
    public const FILTER_STATUSES = 'statuses';
    private bool $schedulingEnabled = true;

    public function selectSchedulingEnabled(bool $enabled = true) : self
    {
        $this->schedulingEnabled = $enabled;
        return $this;
    }

    /**
     * @param NewsEntryType $type
     * @return $this
     */
    public function selectType(NewsEntryType $type) : self
    {
        return $this->selectCriteriaValue(self::FILTER_TYPES, $type->getID());
    }

    /**
     * @param NewsEntryStatus $status
     * @return $this
     */
    public function selectStatus(NewsEntryStatus $status) : self
    {
        return $this->selectCriteriaValue(self::FILTER_STATUSES, $status->getID());
    }

    public function selectArticles() : self
    {
        return $this->selectType(NewsEntryTypes::getInstance()->getTypeArticle());
    }

    public function selectAlerts() : self
    {
        return $this->selectType(NewsEntryTypes::getInstance()->getTypeAlert());
    }

    public function selectPublished() : self
    {
        return $this->selectStatus(NewsEntryStatuses::getInstance()->getPublished());
    }

    protected function prepareQuery(): void
    {
        if($this->schedulingEnabled)
        {
            $this->addWhere(NewsCollection::statementBuilder(
                "({date_scheduled_from} IS NULL OR {date_scheduled_from} <= NOW())"
            ));

            $this->addWhere(NewsCollection::statementBuilder(
                "({date_scheduled_to} IS NULL OR {date_scheduled_to} >= NOW())"
            ));
        }

        $this->addWhereColumnIN(NewsCollection::COL_NEWS_TYPE, $this->getCriteriaValues(self::FILTER_TYPES));
        $this->addWhereColumnIN(NewsCollection::COL_STATUS, $this->getCriteriaValues(self::FILTER_STATUSES));
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
