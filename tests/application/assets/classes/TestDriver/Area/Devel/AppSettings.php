<?php
/**
 * File containing the {@link Application_Admin_Area_Devel_AppSettings} class.
 * @package Application
 * @subpackage TestDriver
 * @see Application_Admin_Area_Devel_AppSettings
 */

/**
 * The application interface references with a showcase of UI elements.
 *
 * @package Application
 * @subpackage TestDriver
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Admin_Area_Devel_AppSettings
 */
class TestDriver_Area_Devel_AppSettings extends Application_Admin_Area_Devel_AppSettings
{
    protected function _registerSettings() : void
    {
        $this->registerSetting(
            'test_boolean_setting',
            'boolean',
            t('Test setting: Boolean')
        );
    }
}
