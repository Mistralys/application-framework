<?php

declare(strict_types=1);

namespace NewsCentral\Entries;

use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\NewsCollection;
use AppUtils\BaseException;
use DBHelper;
use function AppUtils\valBoolTrue;

class EntryCategoriesManager
{
    private NewsEntry $entry;
    private CategoriesCollection $collection;

    /**
     * @var int[]|null
     */
    private ?array $categoryIDs = null;

    public function __construct(NewsEntry $entry)
    {
        $this->entry = $entry;
        $this->collection = AppFactory::createNews()->createCategories();
    }

    /**
     * @param array<int,int|string> $categoryIDs
     * @return bool
     */
    public function setCategoryIDs(array $categoryIDs) : bool
    {
        $result = valBoolTrue();

        $result->set($this->clearCategories());

        foreach($categoryIDs as $categoryID)
        {
            $categoryID = (int)$categoryID;

            if($this->collection->idExists($categoryID)) {
                $result->set($this->addCategory($this->collection->getByID($categoryID)));
            }
        }

        return $result->get();
    }

    public function removeCategory(Category $category) : bool
    {
        if(!$this->hasCategory($category)) {
            return false;
        }

        DBHelper::deleteRecords(
            NewsCollection::TABLE_NAME_ENTRY_CATEGORIES,
            array(
                CategoriesCollection::PRIMARY_NAME => $category->getID(),
                NewsCollection::PRIMARY_NAME => $this->entry->getID()
            )
        );

        $this->resetCategoriesCache();

        return true;
    }

    public function addCategory(Category $category) : bool
    {
        if($this->hasCategory($category)) {
            return false;
        }

        DBHelper::insertDynamic(
            NewsCollection::TABLE_NAME_ENTRY_CATEGORIES,
            array(
                CategoriesCollection::PRIMARY_NAME => $category->getID(),
                NewsCollection::PRIMARY_NAME => $this->entry->getID()
            )
        );

        $this->resetCategoriesCache();

        return true;
    }

    public function hasCategory(Category $category) : bool
    {
        return in_array($category->getID(), $this->getCategoryIDs());
    }

    private function resetCategoriesCache() : void
    {
        $this->categoryIDs = null;
    }

    /**
     * @return int[]
     */
    public function getCategoryIDs() : array
    {
        if(isset($this->categoryIDs)) {
            return $this->categoryIDs;
        }

        $query = /** @lang text */<<<'EOT'
SELECT
    {categories_primary}
FROM
    {table_entry_categories}
WHERE
    {news_primary}=:primary
EOT;

        $this->categoryIDs = DBHelper::fetchAllKeyInt(
            CategoriesCollection::PRIMARY_NAME,
            NewsCollection::statementBuilder($query),
            array(
                'primary' => $this->entry->getID()
            )
        );

        return $this->categoryIDs;
    }

    public function hasCategories() : bool
    {
        return $this->countCategories() > 0;
    }

    /**
     * @return Category[]
     * @throws BaseException
     */
    public function getCategories() : array
    {
        return $this->collection
            ->getFilterCriteria()
            ->selectCategoryIDs($this->getCategoryIDs())
            ->getItemsObjects();
    }

    private function clearCategories() : bool
    {
        $result = valBoolTrue();

        $categories = $this->getCategories();

        foreach($categories as $category)
        {
            $result->set($this->removeCategory($category));
        }

        return $result->get();
    }

    public function countCategories() : int
    {
        return count($this->getCategoryIDs());
    }

    public function renderCommaSeparated() : string
    {
        $list = array();
        $categories = $this->getCategories();

        foreach($categories as $category)
        {
            $list[] = (string)sb()->link(
                $category->getLabel(),
                $category->getLiveURL()
            );
        }

        return implode(', ', $list);
    }
}
