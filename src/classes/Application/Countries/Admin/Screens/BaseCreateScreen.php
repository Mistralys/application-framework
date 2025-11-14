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
use UI\AdminURLs\AdminURLInterface;

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

    public function getCanceledURL(): AdminURLInterface
    {
        return AppFactory::createCountries()->adminURL()->list();
    }

    public function getSuccessMessage(): string
    {
        $country = $this->getStepConfirm()->getCreatedCountry();

        return t('The country %1$s has been created successfully at %2$s.',
            sb()->reference($country->getLabel()),
            sb()->time()
        );
    }

    public function getSuccessURL(): string
    {
        return (string)$this
            ->getStepConfirm()
            ->getCreatedCountry()
            ->adminURL()
            ->status();
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

    public function getStepConfirm() : ConfirmStep
    {
        return ClassHelper::requireObjectInstanceOf(
            ConfirmStep::class,
            $this->getStep(ConfirmStep::STEP_NAME)
        );
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
