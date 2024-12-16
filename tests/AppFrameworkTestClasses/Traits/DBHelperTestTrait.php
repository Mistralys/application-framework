<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use DBHelper;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecord;

/**
 * @package Application
 * @subpackage UnitTests
 * @see DBHelperTestInterface
 */
trait DBHelperTestTrait
{
    protected TestDBCollection $DBRecordCollection;

    public function setUpDBHelperTestTrait(): void
    {
        $this->startTransaction();

        $this->DBRecordCollection = TestDBCollection::getInstance();

        // Ensure we're always working with an empty tests table.
        DBHelper::deleteRecords(DBHelperTestInterface::TEST_RECORDS_TABLE);

        DBHelper::resetTrackedQueries();
    }

    public function createTestRecord(?string $label=null, ?string $alias=null) : TestDBRecord
    {
        return TestDBCollection::getInstance()->createTestRecord(
            $label ?? 'Test Record '.$this->getTestCounter('test-records'),
            $alias ?? 'test-record-'.$this->getTestCounter('test-records')
        );
    }
}