<?php

declare(strict_types=1);

/**
 * Usage:
 *
 * <pre>
 * Application_Stubs_Formable_RecordSettingsStub::create();
 * </pre>
 *
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Stubs_Formable_RecordSettingsStub extends Application_Formable_RecordSettings
{
    public static function create() : Application_Stubs_Formable_RecordSettingsStub
    {
        return new Application_Stubs_Formable_RecordSettingsStub(
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
}
