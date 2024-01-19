<?php
/**
 * @package TestDriver
 * @subpackage Test DB Collection
 */

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application\Tags\Taggables\TagContainer;
use Application\Tags\TagRecord;
use DBHelper_BaseCollection;

/**
 * Custom tag container for the test DB, used
 * to implement test DB specific methods to
 * fetch the correct record types.
 *
 * @package TestDriver
 * @subpackage Test DB Collection
 *
 * @method TestDBRecord[] getDBRecordsByTag(TagRecord $tag, DBHelper_BaseCollection $collection)
 */
class TestDBTagContainer extends TagContainer
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
