<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use DBHelper;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecord;

/**
 * @package Application
 * @subpackage UnitTests
 */
abstract class DBHelperTestCase extends ApplicationTestCase
{
    public const TEST_RECORDS_PRIMARY = TestDBCollection::PRIMARY_NAME;
    public const TEST_RECORDS_COL_LABEL = TestDBCollection::COL_LABEL;
    public const TEST_RECORDS_COL_ALIAS = TestDBCollection::COL_ALIAS;
    public const TEST_RECORDS_DATA_TABLE = TestDBCollection::TABLE_NAME_DATA;
    public const TEST_RECORDS_TABLE = TestDBCollection::TABLE_NAME;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        // Ensure we're always working with an empty tests table.
        DBHelper::deleteRecords(self::TEST_RECORDS_TABLE);
    }

    protected function createTestRecord(?string $label=null, ?string $alias=null) : TestDBRecord
    {
        return TestDBCollection::getInstance()->createTestRecord(
            $label ?? 'Test Record '.$this->getTestCounter('test-records'),
            $alias ?? 'test-record-'.$this->getTestCounter('test-records')
        );
    }
}
