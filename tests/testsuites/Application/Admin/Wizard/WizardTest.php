<?php

namespace testsuites\Application\Admin\Wizard;

use Application_Driver;
use AppUtils\ConvertHelper;
use Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase;
use TestDriver_Area_WizardTest_Wizard;

final class WizardTest extends ApplicationTestCase
{
    protected TestDriver_Area_WizardTest_Wizard $wizard;

    public function setUp() : void
    {
        parent::setUp();
        $this->startTransaction();
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

        $this->wizard->changeCountry('UK');
        $countriesStep->process();
        $this->assertFalse($ticketStep->isComplete());
        $this->assertFalse($summaryStep->isComplete());
    }
}
