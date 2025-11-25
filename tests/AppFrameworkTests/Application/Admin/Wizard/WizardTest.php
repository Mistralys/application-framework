<?php

namespace testsuites\Application\Admin\Wizard;

use Application_Countries;
use Application_Driver;
use AppLocalize\Localization\Country\CountryGB;
use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver_Area_WizardTest_Wizard;

final class WizardTest extends ApplicationTestCase
{
    protected TestDriver_Area_WizardTest_Wizard $wizard;

    public function setUp() : void
    {
        parent::setUp();
        $this->startTransaction();

        $this->cleanUpTables(array(Application_Countries::TABLE_NAME));

        $driver = Application_Driver::getInstance();
        $screen = $driver->getScreenByPath('wizardtest.wizard');

        $this->assertNotNull($screen, 'Screen could not be found by path.');
        $this->assertInstanceOf(TestDriver_Area_WizardTest_Wizard::class, $screen);

        $this->wizard = $screen;
    }

    public function test_wizardSteps() : void
    {
        $this->wizard->handleActions();
        $countriesStep = $this->wizard->getStep('Countries');

        $ticketStep = $this->wizard->getStep('Ticket');
        $ticketStep->process();
        $this->assertTrue($ticketStep->isComplete());

        $summaryStep = $this->wizard->getStep('Summary');
        $summaryStep->process();
        $this->assertTrue($summaryStep->isComplete());

        $this->wizard->changeCountry(CountryGB::ISO_CODE);
        $countriesStep->process();
        $this->assertFalse($ticketStep->isComplete());
        $this->assertFalse($summaryStep->isComplete());
    }
}
