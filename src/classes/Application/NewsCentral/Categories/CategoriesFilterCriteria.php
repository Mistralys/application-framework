<?php

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method Category[] getItemsObjects()
 */
class CategoriesFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const string FILTER_CATEGORY_IDS = 'category_ids';

    /**
     * @param array<int,int|string> $ids
     * @return $this
     */
    public function selectCategoryIDs(array $ids) : self
    {
        foreach($ids as $id) {
            $this->selectCategoryID((int)$id);
        }

        return $this;
    }

    public function selectCategoryID(int $id) : self
    {
        if($this->collection->idExists($id)) {
            return $this->selectCriteriaValue(self::FILTER_CATEGORY_IDS, $id);
        }

        return $this;
    }

    protected function prepareQuery(): void
    {
        $this->addWhereColumnIN(
            CategoriesCollection::PRIMARY_NAME,
            $this->getCriteriaValues(self::FILTER_CATEGORY_IDS)
        );
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
