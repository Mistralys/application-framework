<?php
/**
 * @package Test Driver
 * @subpackage Test DB Records
 */

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application_CollectionItemInterface;
use DBHelper\Admin\BaseDBRecordSelectionTieIn;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use TestDriver\ClassFactory;
use UI_Bootstrap_BigSelection_Item_Regular;

/**
 * @package Test Driver
 * @subpackage Test DB Records
 */
class TestDBRecordSelectionTieIn extends BaseDBRecordSelectionTieIn
{
    protected function adjustEntry(UI_Bootstrap_BigSelection_Item_Regular $entry, Application_CollectionItemInterface $record): void
    {
    }

    /**
     * @return TestDBCollection
     */
    public function getCollection(): DBHelper_BaseCollection
    {
        return ClassFactory::createTestDBCollection();
    }

    public function isSelectionRightsBased(): bool
    {
        return false;
    }

    public function getSelectableRecords(): array
    {
        return $this->getCollection()->getAll();
    }
}
