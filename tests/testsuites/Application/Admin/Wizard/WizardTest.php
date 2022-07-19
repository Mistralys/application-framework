<?php

namespace testsuites\Application\Admin\Wizard;

use Application_Driver;
use TestDriver_Area_WizardTest_Wizard;

final class WizardTest extends \Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase
{
    protected TestDriver_Area_WizardTest_Wizard $wizard;

    public function setUp() : void
    {
        parent::setUp();
        $this->startTransaction();
        $driver = Application_Driver::getInstance();
        $screen = $driver->getScreenByPath('wizardtest.wizard');
        if ($screen instanceof TestDriver_Area_WizardTest_Wizard)
        {
            $this->wizard = $screen;
        }
        else
        {
            $this->fail('Wizard could not created.');
        }
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