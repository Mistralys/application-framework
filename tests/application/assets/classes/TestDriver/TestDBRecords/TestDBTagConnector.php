<?php
/**
 * @package TestDriver
 * @subpackage Test DB Collection
 */

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application\Tags\Taggables\TagConnector;
use Application\Tags\TagRecord;
use DBHelper\BaseCollection\DBHelperCollectionInterface;

/**
 * Custom tag container for the test DB, used
 * to implement test DB specific methods to
 * fetch the correct record types.
 *
 * @package TestDriver
 * @subpackage Test DB Collection
 *
 * @method TestDBRecord[] getDBRecordsByTag(TagRecord $tag, DBHelperCollectionInterface $collection)
 */
class TestDBTagConnector extends TagConnector
{
    /**
     * @param TagRecord $tag
     * @return TestDBRecord[]
     */
    public function getByTag(TagRecord $tag) : array
    {
        return $this->getDBRecordsByTag($tag, TestDBCollection::getInstance());
    }
}
