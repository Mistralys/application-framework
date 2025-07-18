<?php
/**
 * @package Application Tests
 * @subpackage Mythology Test Classes
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use DBHelper;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;
use TestDriver\Collection\Admin\MythologicalRecordSelectionTieIn;
use TestDriver\Collection\MythologicalRecord;
use TestDriver\Collection\MythologyRecordCollection;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecord;

/**
 * @package Application Tests
 * @subpackage Mythology Test Classes
 */
trait MythologyTestTrait
{
    protected MythologyRecordCollection $mythologyRecordCollection;

    public function createTestMythologyRecord(?string $recordID=null) : MythologicalRecord
    {
        if($recordID !== null) {
            return $this->mythologyRecordCollection->getByID($recordID);
        }

        return $this->mythologyRecordCollection->getRandom();
    }

    public function setUpMythologyTestTrait() : void
    {
        $this->mythologyRecordCollection = MythologyRecordCollection::getInstance();
    }

    public function createTestRecordTieIn() : MythologicalRecordSelectionTieIn
    {
        $screen = ClassFactory::createDriver()->getScreenByPath(TestingScreen::URL_NAME);

        return new MythologicalRecordSelectionTieIn($screen);
    }
}
