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
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use DBHelper_Exception;

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
     * @param DBHelper_BaseCollection $collection
     * @return DBHelper_BaseRecord[]
     *
     * @throws \Application\Exception\DisposableDisposedException
     * @throws DBHelper_Exception
     */
    protected function getDBRecordsByTag(TagRecord $tag, DBHelper_BaseCollection $collection) : array
    {
        $ids = $this->getRecordIDsByTag($tag);
        $result = array();

        foreach ($ids as $id) {
            $result[] = $collection->getByID($id);
        }

        return $result;
    }
}
