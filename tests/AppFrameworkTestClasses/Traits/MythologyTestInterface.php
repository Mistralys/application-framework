<?php
/**
 * @package Application Tests
 * @subpackage Mythology Test Classes
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use TestDriver\Collection\Admin\MythologicalRecordSelectionTieIn;
use TestDriver\Collection\MythologicalRecord;
use TestDriver\Collection\MythologyRecordCollection;

/**
 * @package Application Tests
 * @subpackage Mythology Test Classes
 */
interface MythologyTestInterface extends ApplicationTestCaseInterface
{
    /**
     * @param string|null $recordID If no record is specified, a random one is used.
     * @return MythologicalRecord
     * @see MythologyRecordCollection::RECORD_ATHENA Example record ID
     */
    public function createTestMythologyRecord(?string $recordID=null) : MythologicalRecord;

    public function setUpMythologyTestTrait() : void;

    public function createTestRecordTieIn() : MythologicalRecordSelectionTieIn;
}
