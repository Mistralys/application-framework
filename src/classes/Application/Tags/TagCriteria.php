<?php

declare(strict_types=1);

namespace Application\Tags;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @property TagCollection $collection
 */
class TagCriteria extends DBHelper_BaseFilterCriteria
{
    public const FILTER_PARENT_TAGS = 'parent_tags';

    private bool $rootTags = false;

    public function selectParentTag(TagRecord $tag) : self
    {
        return $this->selectCriteriaValue(self::FILTER_PARENT_TAGS, $tag->getID());
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
        $container
            ->table('{table_tags}', TagCollection::TABLE_NAME)
            ->field('{tag_primary}', TagCollection::PRIMARY_NAME)
            ->field('{tag_label}', TagCollection::COL_LABEL)
            ->field('{tag_parent_id}', TagCollection::COL_PARENT_TAG_ID);
    }

    protected function prepareQuery(): void
    {
        if($this->rootTags) {
            $this->addWhereColumnISNULL(TagCollection::COL_PARENT_TAG_ID);
        }

        $this->addWhereColumnIN(TagCollection::COL_PARENT_TAG_ID, $this->getCriteriaValues(self::FILTER_PARENT_TAGS));
    }

    /**
     * Selects only root tags (tags without a parent tag).
     * @return $this
     */
    public function selectRootTags() : self
    {
        $this->rootTags = true;
        return $this;
    }
}
