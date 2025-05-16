<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Create;

use Application\AppFactory;
use Application_Formable_RecordSettings;
use Application_Interfaces_Admin_Wizard_SettingsManagerStep;
use Application_Traits_Admin_Wizard_SettingsManagerStep;

class CountrySettingsStep extends BaseCreateStep implements Application_Interfaces_Admin_Wizard_SettingsManagerStep
{
    use Application_Traits_Admin_Wizard_SettingsManagerStep;

    public const STEP_NAME = 'CountrySettings';

    public function getID(): string
    {
        return self::STEP_NAME;
    }

    public function render(): string
    {
        return 'Settings!';
    }

    protected function preProcess(): void
    {
    }

    public function getLabel(): string
    {
        return t('Settings');
    }

    public function getAbstract(): string
    {
        return t('Please complete the country configuration by reviewing its settings.');
    }

    public function createSettingsManager(): Application_Formable_RecordSettings
    {
        return AppFactory::createCountries()->createSettingsManager($this, null);
    }
}
