<?php
/**
 * @package Tagging
 * @subpackage Taggables
 */

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use DBHelper;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Helper class that can be used to retrieve records
 * from a tag connection table. It automates retrieval
 * tasks to avoid implementing them manually.
 *
 * @package Tagging
 * @subpackage Taggables
 */
class TagConnector
{
    private string $primaryName;
    private string $tableName;
    private TagCollectionInterface $collection;

    /**
     * @param TagCollectionInterface $collection
     */
    public function __construct(TagCollectionInterface $collection)
    {
        $this->collection = $collection;
        $this->primaryName = $collection->getTagPrimary();
        $this->tableName = $collection->getTagTable();
    }

    public function getCollection() : TagCollectionInterface
    {
        return $this->collection;
    }

    public function getPrimaryName() : string
    {
        return $this->primaryName;
    }

    public function getTableName() : string
    {
        return $this->tableName;
    }

    public function getRecordIDsByTag(TagRecord $tag) : array
    {
        return DBHelper::createFetchMany($this->tableName)
            ->whereValue(TagCollection::PRIMARY_NAME, $tag->getID())
            ->fetchColumnInt($this->primaryName);
    }

    /**
     * @param TagRecord $tag
     * @param DBHelperCollectionInterface $collection
     * @return DBHelperRecordInterface[]
     */
    protected function getDBRecordsByTag(TagRecord $tag, DBHelperCollectionInterface $collection) : array
    {
        $ids = $this->getRecordIDsByTag($tag);
        $result = array();

        foreach ($ids as $id) {
            $result[] = $collection->getByID($id);
        }

        return $result;
    }
}
