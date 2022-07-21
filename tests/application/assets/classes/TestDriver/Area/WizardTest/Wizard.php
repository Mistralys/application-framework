<?php

declare(strict_types=1);

class TestDriver_Area_WizardTest_Wizard extends Application_Admin_Wizard
{
    public const URL_NAME = 'wizard';

    protected function init() : void
    {
        parent::init();
    }

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    protected function _initSteps() : void
    {
        $countries = new Application_Countries();
        $countries->createNewCountry('uk', 'United Kingdom');
        $countries->createNewCountry('de', 'Germany');
        $countries->createNewCountry('mx', 'Mexico');
        $this->changeCountry('DE');

        $this->addStep('Countries');
        $this->addStep('Ticket');
        $this->addStep('Summary');
    }

    public function handle_stepUpdated(Application_Admin_Wizard_Step $updatedStep) : void
    {
        $number = $updatedStep->getNumber();
        foreach ($this->steps as $step)
        {
            if ($step->getNumber() > $number && $step->isMonitoring($updatedStep))
            {
                $step->handle_stepUpdated($updatedStep);
            }
        }
    }

    public function changeCountry(string $isoCode) : void {
        $this->setWizardSetting('country_id', Application_Countries::getInstance()->getByISO($isoCode)->getID());
    }

    public function isUserAllowed() : bool
    {
        return true;
    }

    public function getNavigationTitle() : string
    {
        return 'Test wizard';
    }

    public function getTitle() : string
    {
        return 'Test wizard';
    }

    public function getWizardID() : string
    {
        return 'TestWizard';
    }

    public function getClassBase() : string
    {
        return __CLASS__;
    }

    public function getSuccessMessage() : string
    {
        return 'The test wizard worked correctly.';
    }

    protected function processCancelCleanup() : void
    {
    }

    public function getPreselectedCountryID()
    {
        return $this->getWizardSetting('country_id');
    }

    public function initSteps(string $reason) : void
    {
        parent::initSteps($reason);
    }
}