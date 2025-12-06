<?php
/**
 * @package Test Driver
 * @subpackage Test DB Records
 */

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application\Collection\CollectionItemInterface;
use DBHelper\Admin\BaseDBRecordSelectionTieIn;
use TestDriver\ClassFactory;
use UI_Bootstrap_BigSelection_Item_Regular;

/**
 * @package Test Driver
 * @subpackage Test DB Records
 */
class TestDBRecordSelectionTieIn extends BaseDBRecordSelectionTieIn
{
    protected function adjustEntry(UI_Bootstrap_BigSelection_Item_Regular $entry, CollectionItemInterface $record): void
    {
    }

    public function getAbstract(): ?string
    {
        return t('Please select a test DB record.');
    }

    public function getCollection(): TestDBCollection
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
