<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\DBHelper;

use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;
use TestDriver_TestDBCollection;

/**
 * @package Application
 * @subpackage UnitTests
 */
class RecordTests extends DBHelperTestCase
{
    public function test_persistChanges() : void
    {
        $collection = new TestDriver_TestDBCollection();
        $record = $collection->createTestRecord('My label', 'my-alias');

        $record->setLabel('New label');
        $record->setAlias('new-alias');
        $record->save();

        $collection->resetCollection();

        $freshRecord = $collection->getByID($record->getID());
        $this->assertNotSame($freshRecord, $record);
        $this->assertSame('New label', $freshRecord->getLabel());
        $this->assertSame('new-alias', $freshRecord->getAlias());
    }

    /**
     * Test for a bug where modifying only the custom fields
     * of a record would cause a query error on saving, because
     * the record attempted to save the record's properties,
     * of which none were actually modified. This resulted in
     * an erroneous `SET` statement without any fields to set.
     *
     * @see \DBHelper_BaseRecord::saveDataKeys()
     */
    public function test_saveModified() : void
    {
        $collection = new TestDriver_TestDBCollection();
        $record = $collection->createTestRecord('My label', 'my-alias');

        $this->assertFalse($record->isModified());

        $record->setCustomField('dummy', 'dummy');

        $this->assertTrue($record->isModified());

        $record->save();

        $this->addToAssertionCount(1);
    }
}
