<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBRecordSelectionTieIn;

final class RecordTieInTests extends ApplicationTestCase
{
    public function test_enabledCallback() : void
    {
        $screen = ClassFactory::createDriver()->getScreenByPath(TestingScreen::URL_NAME);
        $tieIn = new TestDBRecordSelectionTieIn($screen);

        $this->assertTrue($tieIn->isEnabled());

        $tieIn->setEnabledCallback(function() : bool {
            return false;
        });

        $this->assertFalse($tieIn->isEnabled());
    }
}
