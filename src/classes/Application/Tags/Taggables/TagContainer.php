<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use Application_Exception_DisposableDisposed;
use DBHelper;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use DBHelper_Exception;

/**
 * Helper class that can be used to retrieve records
 * from a tag connection table. It automates retrieval
 * tasks to avoid implementing them manually.
 *
 * @package Application
 * @subpackage Tags
 */
class TagContainer
{
    private string $primaryName;
    private string $tableName;

    /**
     * @param string $tableName Name of the table storing the record-tag connections.
     * @param string $primaryName Primary key column name of the record to tag.
     */
    public function __construct(string $tableName, string $primaryName)
    {
        $this->primaryName = $primaryName;
        $this->tableName = $tableName;
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
     * @throws Application_Exception_DisposableDisposed
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
