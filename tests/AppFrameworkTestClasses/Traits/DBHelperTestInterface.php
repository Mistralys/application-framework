<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecord;

/**
 * @package Application
 * @subpackage UnitTests
 * @see DBHelperTestTrait
 */
interface DBHelperTestInterface
{
    public const TEST_RECORDS_PRIMARY = TestDBCollection::PRIMARY_NAME;
    public const TEST_RECORDS_COL_LABEL = TestDBCollection::COL_LABEL;
    public const TEST_RECORDS_DATA_TABLE = TestDBCollection::TABLE_NAME_DATA;
    public const TEST_RECORDS_TABLE = TestDBCollection::TABLE_NAME;
    public const TEST_RECORDS_COL_ALIAS = TestDBCollection::COL_ALIAS;

    public function setUpDBHelperTestTrait(): void;
    public function createTestRecord(?string $label=null, ?string $alias=null) : TestDBRecord;
}
