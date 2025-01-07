<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBRecordSelectionTieIn;

final class RecordTieInTests extends ApplicationTestCase
{
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

    public function test_inheritRequestVars() : void
    {
        $tieIn = $this->createTestTieIn();

        $this->assertNull($tieIn->getURL()->getParam('inherit'));

        $_REQUEST['inherit'] = 'foo-x';

        $tieIn->inheritRequestVar('inherit');

        $this->assertSame('foo-x', $tieIn->getURL()->getParam('inherit'));
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
