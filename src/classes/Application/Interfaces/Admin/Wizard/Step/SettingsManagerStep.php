<?php

declare(strict_types=1);

interface Application_Interfaces_Admin_Wizard_SettingsManagerStep extends Application_Interfaces_Admin_Wizard_Step
{
    const KEY_FORM_VALUES = 'values';

    public function getAbstract() : string;

    public function createSettingsManager() : Application_Formable_RecordSettings;
}
