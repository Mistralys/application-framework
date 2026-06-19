<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\LookupItems;

use Application\LookupItems\BaseDBCollectionLookupItem;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecord;

/**
 * Concrete lookup item implementation backed by the framework test DB collection.
 * Used exclusively in {@see \AppFrameworkTests\LookupItems\BaseLookupItemTest}.
 *
 * @package Application
 * @subpackage UnitTests
 */
class TestLookupItem extends BaseDBCollectionLookupItem
{
    public function getFieldLabel() : string
    {
        return 'Test Records';
    }

    public function getFieldDescription() : string
    {
        return 'Search for test records.';
    }

    protected function getCollection() : DBHelperCollectionInterface
    {
        return TestDBCollection::getInstance();
    }

    protected function _getSearchColumns() : array
    {
        return array(
            self::TABLE_ALIAS.'.`'.TestDBCollection::COL_LABEL.'`'
        );
    }

    protected function renderLabel(object $record) : string
    {
        /** @var TestDBRecord $record */
        return $record->getLabel();
    }

    protected function getURL(object $record) : string
    {
        return '#test';
    }
}
