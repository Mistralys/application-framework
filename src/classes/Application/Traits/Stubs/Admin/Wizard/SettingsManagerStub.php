<?php

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Traits_Stubs_Admin_Wizard_SettingsManagerStub
    extends Application_Admin_Wizard_Step
    implements Application_Interfaces_Admin_Wizard_SettingsManagerStep
{
    use Application_Traits_Admin_Wizard_SettingsManagerStep;

    public function isMode() : bool
    {
        return false;
    }

    public function isSubmode() : bool
    {
        return false;
    }

    public function isAction() : bool
    {
        return false;
    }

    public function createSettingsManager() : Application_Formable_RecordSettings
    {
        return Application_Stubs_Formable_RecordSettingsStub::create();
    }

    public function getAbstract() : string
    {
        return '';
    }

    public function initDone()
    {

    }

    protected function init()
    {

    }

    protected function preProcess()
    {

    }
}
