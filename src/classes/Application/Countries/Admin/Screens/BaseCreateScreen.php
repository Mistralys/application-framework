<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens;

use Application\AppFactory;
use Application\Countries\Admin\Screens\Create\ConfirmStep;
use Application\Countries\Admin\Screens\Create\CountrySettingsStep;
use Application\Countries\Admin\Screens\Create\SourceCountrySelectionStep;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Wizard;
use AppUtils\ClassHelper;

abstract class BaseCreateScreen extends Application_Admin_Wizard
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'create';
    public const WIZARD_ID = 'CreateAppCountry';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Create a new country');
    }

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_CREATE;
    }

    public function getNavigationTitle(): string
    {
        return t('Create a country');
    }

    public function getWizardID(): string
    {
        return self::WIZARD_ID;
    }

    public function getClassBase(): string
    {
        // Not using this, we provide all the step class names.
        return '';
    }

    public function getCanceledURL(): string
    {
        return (string)AppFactory::createCountries()->adminURL()->list();
    }

    public function getSuccessMessage(): string
    {
        return t('The country has been created successfully at %1$s.',
            sb()->time()
        );
    }

    protected function processCancelCleanup(): void
    {

    }

    protected function _initSteps(): void
    {
        $this->addStep(SourceCountrySelectionStep::STEP_NAME, SourceCountrySelectionStep::class);
        $this->addStep(CountrySettingsStep::STEP_NAME, CountrySettingsStep::class);
        $this->addStep(ConfirmStep::STEP_NAME, ConfirmStep::class);
    }

    public function getStepSourceCountry() : SourceCountrySelectionStep
    {
        return ClassHelper::requireObjectInstanceOf(
            SourceCountrySelectionStep::class,
            $this->getStep(SourceCountrySelectionStep::STEP_NAME)
        );
    }

    public function getStepSettings() : CountrySettingsStep
    {
        return ClassHelper::requireObjectInstanceOf(
            CountrySettingsStep::class,
            $this->getStep(CountrySettingsStep::STEP_NAME)
        );
    }
}
