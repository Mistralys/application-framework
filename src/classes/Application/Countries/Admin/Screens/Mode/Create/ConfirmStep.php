<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Mode\Create;

use Application\AppFactory;
use Application_Countries_Country;
use Application_Interfaces_Admin_Wizard_Step_Confirmation;
use Application_Traits_Admin_Wizard_Step_Confirmation;
use UI_PropertiesGrid;

class ConfirmStep extends BaseCreateStep implements Application_Interfaces_Admin_Wizard_Step_Confirmation
{
    use Application_Traits_Admin_Wizard_Step_Confirmation;

    public function getID(): string
    {
        return self::STEP_NAME;
    }

    protected function preProcess(): void
    {
    }

    public function getLabel(): string
    {
        return t('Confirm');
    }

    public function getAbstract(): string
    {
        return t('Please review your settings before continuing.');
    }

    protected function createReferenceID(): string
    {
        $country = $this->countries->createNewCountry(
            $this->wizard->getStepSourceCountry()->requireCountry()->getCode(),
            $this->wizard->getStepSettings()->getCountryLabel()
        );

        return (string)$country->getID();
    }

    protected function populateSummaryGrid(UI_PropertiesGrid $grid): void
    {
        $grid->add(t('Country'), strtoupper($this->wizard->getStepSourceCountry()->requireCountry()->getCode()));
        $grid->add(t('Label'), $this->wizard->getStepSettings()->getCountryLabel());
    }

    public function getCreatedCountry() : Application_Countries_Country
    {
        return AppFactory::createCountries()->getCountryByID((int)$this->getReferenceID());
    }
}
