<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\DBHelper;

use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBCollection;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;
use TestDriver\TestDBRecords\TestDBRecord;

/**
 * @package Application
 * @subpackage UnitTests
 */
class RecordTests extends DBHelperTestCase
{
    /**
     * Added this test after realizing that the collection
     * created by the class factory was a different instance
     * in an unrelated test.
     */
    public function test_sameCollectionInstances() : void
    {
        $this->assertSame(ClassFactory::createTestDBCollection(), TestDBCollection::getInstance());
    }

    public function test_persistChanges(): void
    {
        $collection = new TestDBCollection();
        $record = $collection->createTestRecord('My label', 'my-alias');

        $record->setLabel('New label');
        $record->setAlias('new-alias');
        $record->save();

        $collection->resetCollection();

        $freshRecord = $collection->getByID($record->getID());

        $this->assertNotSame($freshRecord, $record);
        $this->assertInstanceOf(TestDBRecord::class, $freshRecord);
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
    public function test_saveOnlyCustomModified(): void
    {
        $collection = new TestDBCollection();
        $record = $collection->createTestRecord('My label', 'my-alias');

        $this->assertFalse($record->isModified());

        $record->setCustomField('dummy', 'dummy');

        $this->assertTrue($record->isModified());
        $this->assertFalse($record->isStructureModified(), 'The custom field is not structural.');

        $record->save();

        $this->addToAssertionCount(1);
    }

    public function test_structural(): void
    {
        $collection = new TestDBCollection();
        $record = $collection->createTestRecord('My label', 'my-alias');

        $record->setAlias('new-alias');

        $this->assertTrue($record->isModified());
        $this->assertTrue($record->isStructureModified(), 'The alias is a structural key.');

        $record->save();

        $this->assertFalse($record->isStructureModified());
    }
}
