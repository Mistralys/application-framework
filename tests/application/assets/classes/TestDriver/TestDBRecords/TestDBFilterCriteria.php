<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaTrait;
use Application\Tags\Taggables\TagCollectionInterface;
use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method TestDBRecord[] getItemsObjects()
 */
class TestDBFilterCriteria extends DBHelper_BaseFilterCriteria implements TaggableFilterCriteriaInterface
{
    use TaggableFilterCriteriaTrait;

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }

    public function getTagContainer(): TagCollectionInterface
    {
        return TestDBCollection::getInstance();
    }
}
