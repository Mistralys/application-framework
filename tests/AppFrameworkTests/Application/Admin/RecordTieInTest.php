<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\DBHelperTestInterface;
use AppFrameworkTestClasses\Traits\DBHelperTestTrait;
use AppFrameworkTestClasses\Traits\MythologyTestInterface;
use AppFrameworkTestClasses\Traits\MythologyTestTrait;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;
use TestDriver\Collection\Admin\MythologicalRecordSelectionTieIn;
use TestDriver\Collection\MythologyRecordCollection;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecordSelectionTieIn;

final class RecordTieInTest extends ApplicationTestCase implements DBHelperTestInterface, MythologyTestInterface
{
    use DBHelperTestTrait;
    use MythologyTestTrait;

    // region: _Tests

    public function test_enabledCallback() : void
    {
        $tieIn = $this->createTestDBRecordTieIn();

        $this->assertTrue($tieIn->isEnabled());

        $tieIn->setEnabledCallback(function() : bool {
            return false;
        });

        $this->assertFalse($tieIn->isEnabled());
    }

    public function test_noRecordSelectedByDefault() : void
    {
        $tieIn = $this->createTestDBRecordTieIn();

        $this->assertFalse($tieIn->isRecordSelected());
        $this->assertNull($tieIn->getRecordID());
        $this->assertNull($tieIn->getRecord());
    }

    public function test_getSelectedRecord() : void
    {
        $tieIn = $this->createTestDBRecordTieIn();
        $testRecord = $this->createTestDBRecord();

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();

        $this->assertNotNull(ClassFactory::createTestDBCollection()->getByRequest(), 'The record should be found by the collection in the request.');

        $this->assertTrue($tieIn->isRecordSelected());
        $this->assertSame($testRecord->getID(), $tieIn->getRecordID());
        $this->assertSame($testRecord, $tieIn->getRecord());
    }

    public function test_inheritRequestVars() : void
    {
        $tieIn = $this->createTestDBRecordTieIn();

        $this->assertNull($tieIn->getURL()->getParam('inherit'));

        $_REQUEST['inherit'] = 'foo-x';

        $tieIn->inheritRequestVar('inherit');

        $this->assertSame('foo-x', $tieIn->getURL()->getParam('inherit'));
    }

    public function test_primaryValueIsAddedToTheURL() : void
    {
        $tieIn = $this->createTestDBRecordTieIn();
        $testRecord = $this->createTestDBRecord();

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();

        $this->assertSame($testRecord->getID(), $tieIn->getRecordID());
        $this->assertSame($testRecord->getID(), (int)$tieIn->getURL()->getParam(TestDBCollection::REQUEST_PRIMARY_NAME));
    }

    public function test_hiddenVariablesArePresent() : void
    {
        $tieIn = $this->createTestRecordTieIn();
        $testRecord = $this->createTestMythologyRecord();

        // Simulate the record being selected in the request
        $_REQUEST[MythologyRecordCollection::REQUEST_VAR_NAME] = $testRecord->getID();

        $expected = array(
            MythologicalRecordSelectionTieIn::HIDDEN_VAR_NAME => MythologicalRecordSelectionTieIn::HIDDEN_VAR_VALUE,
            MythologyRecordCollection::REQUEST_VAR_NAME => $testRecord->getID()
        );

        ksort($expected); // Hidden vars are always sorted alphabetically

        $this->assertSame(
            $expected,
            $tieIn->getHiddenVars()
        );
    }

    public function test_primaryValueCanBeCombinedWithInheritVar() : void
    {
        $tieIn = $this->createTestDBRecordTieIn();
        $testRecord = $this->createTestDBRecord();

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();
        $_REQUEST['inherit'] = 'foo-z';

        $tieIn->inheritRequestVar('inherit');

        $url = $tieIn->getURL();

        $this->assertSame($testRecord->getID(), (int)$url->getParam(TestDBCollection::REQUEST_PRIMARY_NAME));
        $this->assertSame('foo-z', $url->getParam('inherit'));
    }

    public function test_ancestryHandling() : void
    {
        $testRecord = $this->createTestDBRecord();
        $parentTieIn = $this->createTestDBRecordTieIn();
        $childTieIn = new MythologicalRecordSelectionTieIn($parentTieIn->getScreen(), null, $parentTieIn);

        $this->assertSame($parentTieIn, $childTieIn->getParent());
        $this->assertSame(array($parentTieIn), $childTieIn->getAncestry());

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();

        $this->assertTrue($parentTieIn->isRecordSelected());
        $this->assertFalse($parentTieIn->isEnabled());
        $this->assertTrue($childTieIn->isEnabled());
        $this->assertSame($testRecord->getID(), (int)$childTieIn->getURL()->getParam(TestDBCollection::REQUEST_PRIMARY_NAME));
    }

    // endregion
}
