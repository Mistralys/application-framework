<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\DBHelperTestInterface;
use AppFrameworkTestClasses\Traits\DBHelperTestTrait;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecordSelectionTieIn;

final class RecordTieInTests extends ApplicationTestCase implements DBHelperTestInterface
{
    use DBHelperTestTrait;

    // region: _Tests

    public function test_enabledCallback() : void
    {
        $tieIn = $this->createTestTieIn();

        $this->assertTrue($tieIn->isEnabled());

        $tieIn->setEnabledCallback(function() : bool {
            return false;
        });

        $this->assertFalse($tieIn->isEnabled());
    }

    public function test_noRecordSelectedByDefault() : void
    {
        $tieIn = $this->createTestTieIn();

        $this->assertFalse($tieIn->isRecordSelected());
        $this->assertNull($tieIn->getRecordID());
        $this->assertNull($tieIn->getRecord());
    }

    public function test_getSelectedRecord() : void
    {
        $tieIn = $this->createTestTieIn();
        $testRecord = $this->createTestRecord();

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();

        $this->assertNotNull(ClassFactory::createTestDBCollection()->getByRequest(), 'The record should be found by the collection in the request.');

        $this->assertTrue($tieIn->isRecordSelected());
        $this->assertSame($testRecord->getID(), $tieIn->getRecordID());
        $this->assertSame($testRecord, $tieIn->getRecord());
    }

    public function test_inheritRequestVars() : void
    {
        $tieIn = $this->createTestTieIn();

        $this->assertNull($tieIn->getURL()->getParam('inherit'));

        $_REQUEST['inherit'] = 'foo-x';

        $tieIn->inheritRequestVar('inherit');

        $this->assertSame('foo-x', $tieIn->getURL()->getParam('inherit'));
    }

    public function test_primaryValueIsAddedToTheURL() : void
    {
        $tieIn = $this->createTestTieIn();
        $testRecord = $this->createTestRecord();

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();

        $this->assertSame($testRecord->getID(), $tieIn->getRecordID());
        $this->assertSame($testRecord->getID(), (int)$tieIn->getURL()->getParam(TestDBCollection::REQUEST_PRIMARY_NAME));
    }

    public function test_primaryValueCanBeCombinedWithInheritVar() : void
    {
        $tieIn = $this->createTestTieIn();
        $testRecord = $this->createTestRecord();

        // Simulate the record being selected in the request
        $_REQUEST[TestDBCollection::REQUEST_PRIMARY_NAME] = $testRecord->getID();
        $_REQUEST['inherit'] = 'foo-z';

        $tieIn->inheritRequestVar('inherit');

        $url = $tieIn->getURL();

        $this->assertSame($testRecord->getID(), (int)$url->getParam(TestDBCollection::REQUEST_PRIMARY_NAME));
        $this->assertSame('foo-z', $url->getParam('inherit'));
    }

    // endregion

    // region: Support methods

    public function createTestTieIn() : TestDBRecordSelectionTieIn
    {
        $screen = ClassFactory::createDriver()->getScreenByPath(TestingScreen::URL_NAME);

        return new TestDBRecordSelectionTieIn($screen);
    }

    // endregion
}
