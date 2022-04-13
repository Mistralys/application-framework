<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage UnitTests
 */
abstract class DBHelperTestCase extends ApplicationTestCase
{
    public const TEST_RECORDS_PRIMARY = TestDriver_TestDBCollection::PRIMARY_NAME;
    public const TEST_RECORDS_COL_LABEL = TestDriver_TestDBCollection_TestDBRecord::COL_LABEL;
    public const TEST_RECORDS_COL_ALIAS = TestDriver_TestDBCollection_TestDBRecord::COL_ALIAS;
    public const TEST_RECORDS_DATA_TABLE = TestDriver_TestDBCollection::TABLE_NAME_DATA;
    public const TEST_RECORDS_TABLE = TestDriver_TestDBCollection::TABLE_NAME;

    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }
}
