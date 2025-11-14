<?php

declare(strict_types=1);

use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Usage:
 *
 * <pre>
 * Application_Stubs_Formable_RecordSettingsExtendedStub::create();
 * </pre>
 *
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Formable_RecordSettings_Extended
 */
class Application_Stubs_Formable_RecordSettingsExtendedStub extends Application_Formable_RecordSettings_Extended
{
    public static function create() : Application_Stubs_Formable_RecordSettingsExtendedStub
    {
        return new Application_Stubs_Formable_RecordSettingsExtendedStub(
            new Application_Formable_Generic(),
            Application_Countries::getInstance()
        );
    }

    protected function registerSettings() : void
    {

    }

    public function getDefaultSettingName() : string
    {
        return '';
    }

    public function isUserAllowedEditing() : bool
    {
        return Application::getUser()->isDeveloper();
    }

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : void
    {
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : void
    {
    }
}
