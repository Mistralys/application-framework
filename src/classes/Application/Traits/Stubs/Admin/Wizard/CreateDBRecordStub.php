<?php

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Traits_Stubs_Admin_Wizard_CreateDBRecordStub
    extends Application_Admin_Wizard_Step
    implements Application_Interfaces_Admin_Wizard_CreateDBRecordStep
{
    use Application_Traits_Admin_Wizard_CreateDBRecordStep;

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

    public function createSettingsManager() : Application_Formable_RecordSettings_Extended
    {
        return Application_Stubs_Formable_RecordSettingsExtendedStub::create();
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

    public function createCollection() : DBHelper_BaseCollection
    {
        return Application_Countries::getInstance();
    }

    protected function configurePropertiesGrid(UI_PropertiesGrid $grid) : void
    {
    }

    protected function getSettingValues() : array
    {
        return array();
    }
}
