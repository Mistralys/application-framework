<?php

declare(strict_types=1);

use Application\AppFactory;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryGB;
use AppLocalize\Localization\Country\CountryMX;

class TestDriver_Area_WizardTest_Wizard extends Application_Admin_Wizard
{
    public const string URL_NAME = 'wizard';

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
        $this->createCountries();

        $this->changeCountry('DE');

        $this->addStep('Countries');
        $this->addStep('Ticket');
        $this->addStep('Summary');
    }

    private array $requiredCountries = array(
        CountryGB::ISO_CODE => 'United Kingdom',
        CountryDE::ISO_CODE => 'Germany',
        CountryMX::ISO_CODE => 'Mexico'
    );

    private function createCountries() : void
    {
        $countries = AppFactory::createCountries();

        foreach($this->requiredCountries as $iso => $label)
        {
            if($countries->isoExists($iso)) {
                continue;
            }

            $countries->createNewCountry(
                $iso,
                $label
            );
        }
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

    public function getPreselectedCountryID() : int
    {
        return (int)$this->getWizardSetting('country_id');
    }

    public function getPreselectedCountry() : ?Application_Countries_Country
    {
        $collection = AppFactory::createCountries();
        $id = $this->getPreselectedCountryID();

        if($id > 0 && $collection->idExists($id))
        {
            return $collection->getByID($id);
        }

        return null;
    }

    public function initSteps(string $reason) : void
    {
        parent::initSteps($reason);
    }
}