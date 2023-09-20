<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\ConfigSettings\AppConfig
 */

declare(strict_types=1);

namespace Application\ConfigSettings;

/**
 * Static class providing easy access to the application's immutable
 * configuration settings, as defined during the booting.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AppConfig
{
    public static function getURL() : string
    {
        return boot_constant(BaseConfigSettings::URL);
    }

    public static function getRootFolder() : string
    {
        return boot_constant(BaseConfigSettings::ROOT);
    }

    public static function getInstallFolder() : string
    {
        return boot_constant(BaseConfigSettings::INSTALL_FOLDER);
    }

    public static function getInstallURL() : string
    {
        return boot_constant(BaseConfigSettings::INSTALL_URL);
    }

    public static function getVendorFolder() : string
    {
        return boot_constant(BaseConfigSettings::VENDOR_PATH);
    }

    public static function getVendorURL() : string
    {
        return boot_constant(BaseConfigSettings::VENDOR_URL);
    }

    public static function getClassName() : string
    {
        return boot_constant(BaseConfigSettings::CLASS_NAME);
    }

    public static function getCompanyName() : string
    {
        return boot_constant(BaseConfigSettings::COMPANY_NAME);
    }

    public static function getCompanyHomepage() : string
    {
        return boot_constant(BaseConfigSettings::COMPANY_HOMEPAGE);
    }

    public static function getDummyEmail() : string
    {
        return boot_constant(BaseConfigSettings::DUMMY_EMAIL);
    }

    public static function getSystemEmail() : string
    {
        return boot_constant(BaseConfigSettings::SYSTEM_EMAIL);
    }

    public static function getSystemName() : string
    {
        return boot_constant(BaseConfigSettings::SYSTEM_NAME);
    }

    public static function isSessionSimulated() : bool
    {
        return boot_constant(BaseConfigSettings::SIMULATE_SESSION) === true;
    }

    public static function isJavascriptMinified() : bool
    {
        return boot_constant(BaseConfigSettings::JAVASCRIPT_MINIFIED) === true;
    }

    public static function isDemoMode() : bool
    {
        return boot_constant(BaseConfigSettings::DEMO_MODE) === true;
    }

    public static function isLoggingEnabled() : bool
    {
        return boot_constant(BaseConfigSettings::LOGGING_ENABLED) === true;
    }

    public static function isDeveloperMode() : bool
    {
        return boot_constant(BaseConfigSettings::DEVELOPER_MODE) === true;
    }

    public static function isQueryTrackingEnabled() : bool
    {
        return boot_constant(BaseConfigSettings::TRACK_QUERIES) === true;
    }

    public static function isShowQueriesEnabled() : bool
    {
        return boot_constant(BaseConfigSettings::SHOW_QUERIES) === true;
    }

    public static function isAuthenticationEnabled() : bool
    {
        return boot_constant(BaseConfigSettings::NO_AUTHENTICATION) !== true;
    }

    public static function isDBEnabled() : bool
    {
        return boot_constant(BaseConfigSettings::DB_ENABLED) === true;
    }

    public static function getAppsetID() : string
    {
        return boot_constant(BaseConfigSettings::APPSET);
    }

    public static function getEnvironmentID() : string
    {
        return boot_constant(BaseConfigSettings::ENVIRONMENT);
    }

    public static function getRunMode() : string
    {
        return boot_constant(BaseConfigSettings::RUN_MODE);
    }

    public static function getTheme() : string
    {
        return boot_constant(BaseConfigSettings::THEME);
    }
}
