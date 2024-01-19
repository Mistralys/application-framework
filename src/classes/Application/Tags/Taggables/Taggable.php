<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\AppFactory;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use DBHelper;

/**
 * Helper class that can be used to manage tags for a record.
 * It has methods to add and remove tag connections to the
 * record, as stored in the record collection's tag connection
 * table.
 *
 * @package Application
 * @subpackage Tags
 */
class Taggable
{
    private string $tableName;
    private string $primaryName;
    private int $primaryKey;
    private TagContainer $collection;

    /**
     * @param TagContainer $collection Tag collection to use.
     * @param int $primaryKey Primary key value of the record to tag.
     */
    public function __construct(TagContainer $collection, int $primaryKey)
    {
        $this->collection = $collection;
        $this->primaryName = $collection->getPrimaryName();
        $this->tableName = $collection->getTableName();
        $this->primaryKey = $primaryKey;
    }

    public function getCollection() : TagContainer
    {
        return $this->collection;
    }

    public function countTags() : int
    {
        return count($this->getTagIDs());
    }

    public function addTag(TagRecord $tag) : self
    {
        if($this->hasTag($tag))
        {
            return $this;
        }

        DBHelper::requireTransaction('Add a tag to a record');

        DBHelper::insertDynamic(
            $this->tableName,
            array(
                $this->primaryName => $this->primaryKey,
                TagCollection::PRIMARY_NAME => $tag->getID()
            )
        );

        return $this;
    }

    public function hasTag(TagRecord $tag) : bool
    {
        return DBHelper::createFetchKey(TagCollection::PRIMARY_NAME, $this->tableName)
            ->whereValue($this->primaryName, $this->primaryKey)
            ->whereValue(TagCollection::PRIMARY_NAME, $tag->getID())
            ->fetchInt() !== 0;
    }

    public function removeTag(TagRecord $tag) : self
    {
        DBHelper::requireTransaction('Remove a tag from a record');

        DBHelper::deleteRecords(
            $this->tableName,
            array(
                $this->primaryName => $this->primaryKey,
                TagCollection::PRIMARY_NAME => $tag->getID()
            )
        );

        return $this;
    }

    /**
     * @return int[]
     */
    public function getTagIDs() : array
    {
        return DBHelper::createFetchMany($this->tableName)
            ->whereValue($this->primaryName, $this->primaryKey)
            ->fetchColumnInt(TagCollection::PRIMARY_NAME);
    }

    /**
     * @return TagRecord[]
     */
    public function getAll() : array
    {
        $ids = $this->getTagIDs();
        $collection = AppFactory::createTags();
        $result = array();

        foreach($ids as $id)
        {
            $result[] = $collection->getByID($id);
        }

        usort($result, function(TagRecord $a, TagRecord $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $result;
    }

    public function removeAll() : self
    {
        DBHelper::requireTransaction('Remove all tags from a record');

        DBHelper::deleteRecords(
            $this->tableName,
            array(
                $this->primaryName => $this->primaryKey
            )
        );

        return $this;
    }
}
