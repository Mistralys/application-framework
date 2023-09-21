<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\ConfigSettings\BaseConfigRegistry
 */

declare(strict_types=1);

namespace Application\ConfigSettings;

use Application\Environments\BaseEnvironmentsConfig;

// Pre-Autoloading requires.
require_once __DIR__.'/SetConfigSettingInterface.php';
require_once __DIR__.'/SetAppConfigSettingTrait.php';

/**
 * Registry for all available application settings that are
 * set using constants before it is started, depending on
 * the environment that is detected.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see SetAppConfigSettingTrait Setter methods for all settings.
 * @see BaseEnvironmentsConfig Settings are configured via this class in the concrete application.
 */
abstract class BaseConfigRegistry implements SetConfigSettingInterface
{
    use SetAppConfigSettingTrait;

    /**
     * @see APP_SIMULATE_SESSION
     */
    public const SIMULATE_SESSION = 'APP_SIMULATE_SESSION';

    /**
     * @see APP_JAVASCRIPT_MINIFIED
     */
    public const JAVASCRIPT_MINIFIED = 'APP_JAVASCRIPT_MINIFIED';

    /**
     * @see APP_DEMO_MODE
     */
    public const DEMO_MODE = 'APP_DEMO_MODE';

    /**
     * @see APP_COMPANY_NAME
     */
    public const COMPANY_NAME = 'APP_COMPANY_NAME';

    /**
     * @see APP_COMPANY_HOMEPAGE
     */
    public const COMPANY_HOMEPAGE = 'APP_COMPANY_HOMEPAGE';

    /**
     * @see APP_DUMMY_EMAIL
     */
    public const DUMMY_EMAIL = 'APP_DUMMY_EMAIL';

    /**
     * @see APP_SYSTEM_EMAIL
     */
    public const SYSTEM_EMAIL = 'APP_SYSTEM_EMAIL';

    /**
     * @see APP_SYSTEM_NAME
     */
    public const SYSTEM_NAME = 'APP_SYSTEM_NAME';

    /**
     * @see APP_CLASS_NAME
     */
    public const CLASS_NAME = 'APP_CLASS_NAME';

    /**
     * @see APP_ROOT
     * @immutable
     */
    public const ROOT = 'APP_ROOT';

    /**
     * @see APP_INSTALL_FOLDER
     * @immutable
     */
    public const INSTALL_FOLDER = 'APP_INSTALL_FOLDER';

    /**
     * @see APP_VENDOR_PATH
     * @immutable
     */
    public const VENDOR_PATH = 'APP_VENDOR_PATH';

    /**
     * @see APP_URL
     */
    public const URL = 'APP_URL';

    /**
     * @see APP_VENDOR_URL
     */
    public const VENDOR_URL = 'APP_VENDOR_URL';

    /**
     * @see APP_CONTENT_LOCALES
     */
    public const CONTENT_LOCALES = 'APP_CONTENT_LOCALES';

    /**
     * @see APP_UI_LOCALES
     */
    public const UI_LOCALES = 'APP_UI_LOCALES';

    /**
     * @see APP_REQUEST_LOG_PASSWORD
     */
    public const REQUEST_LOG_PASSWORD = 'APP_REQUEST_LOG_PASSWORD';

    /**
     * @see APP_INSTALL_URL
     */
    public const INSTALL_URL = 'APP_INSTALL_URL';

    /**
     * @see APP_APPSET
     */
    public const APPSET = 'APP_APPSET';

    /**
     * @see APP_LOGGING_ENABLED
     */
    public const LOGGING_ENABLED = 'APP_LOGGING_ENABLED';

    /**
     * @see APP_ENVIRONMENT
     */
    public const ENVIRONMENT = 'APP_ENVIRONMENT';

    /**
     * @see APP_DEVELOPER_MODE
     */
    public const DEVELOPER_MODE = 'APP_DEVELOPER_MODE';

    /**
     * @see APP_INSTANCE_ID
     */
    public const INSTANCE_ID = 'APP_INSTANCE_ID';

    /**
     * @see APP_AUTOMATIC_DELETION_DELAY
     */
    public const AUTOMATIC_DELETION_DELAY = 'APP_AUTOMATIC_DELETION_DELAY';

    /**
     * @see APP_SHOW_QUERIES
     */
    public const SHOW_QUERIES = 'APP_SHOW_QUERIES';

    /**
     * @see APP_TRACK_QUERIES
     */
    public const TRACK_QUERIES = 'APP_TRACK_QUERIES';

    /**
     * @see APP_RUN_MODE
     */
    public const RUN_MODE = 'APP_RUN_MODE';

    /**
     * @see APP_FRAMEWORK_TESTS
     */
    public const FRAMEWORK_TESTS = 'APP_FRAMEWORK_TESTS';

    /**
     * @see APP_TESTS_RUNNING
     */
    public const TESTS_RUNNING = 'APP_TESTS_RUNNING';

    /**
     * @see APP_NO_AUTHENTICATION
     */
    public const NO_AUTHENTICATION = 'APP_NO_AUTHENTICATION';

    /**
     * @see APP_THEME
     */
    public const THEME = 'APP_THEME';

    /**
     * @see APP_DB_ENABLED
     */
    public const DB_ENABLED = 'APP_DB_ENABLED';

    /**
     * @see APP_DB_NAME
     */
    public const DB_NAME = 'APP_DB_NAME';

    /**
     * @see APP_DB_HOST
     */
    public const DB_HOST = 'APP_DB_HOST';

    /**
     * @see APP_DB_USER
     */
    public const DB_USER = 'APP_DB_USER';

    /**
     * @see APP_DB_PASSWORD
     */
    public const DB_PASSWORD = 'APP_DB_PASSWORD';

    /**
     * @see APP_DB_PORT
     */
    public const DB_PORT = 'APP_DB_PORT';

    /**
     * @see APP_DB_TESTS_NAME
     */
    public const DB_TESTS_NAME = 'APP_DB_TESTS_NAME';

    /**
     * @see APP_DB_TESTS_USER
     */
    public const DB_TESTS_USER = 'APP_DB_TESTS_USER';

    /**
     * @see APP_DB_TESTS_PORT
     */
    public const DB_TESTS_PORT = 'APP_DB_TESTS_PORT';

    /**
     * @see APP_DB_TESTS_PASSWORD
     */
    public const DB_TESTS_PASSWORD = 'APP_DB_TESTS_PASSWORD';

    /**
     * @see APP_DB_TESTS_HOST
     */
    public const DB_TESTS_HOST = 'APP_DB_TESTS_HOST';

    /**
     * @see APP_CAS_HOST
     */
    public const CAS_HOST = 'APP_CAS_HOST';

    /**
     * @see APP_CAS_PORT
     */
    public const CAS_PORT = 'APP_CAS_PORT';

    /**
     * @see APP_CAS_SERVER_URI
     */
    public const CAS_SERVER_URI = 'APP_CAS_SERVER_URI';

    /**
     * @see APP_CAS_LOGOUT_URL
     */
    public const CAS_LOGOUT_URL = 'APP_CAS_LOGOUT_URL';

    /**
     * @see APP_CAS_NAME
     */
    public const CAS_NAME = 'APP_CAS_NAME';

    /**
     * @see APP_DEEPL_API_KEY
     */
    public const DEEPL_API_KEY = 'APP_DEEPL_API_KEY';

    /**
     * @see APP_DEEPL_PROXY_URL
     */
    public const DEEPL_PROXY_URL = 'APP_DEEPL_PROXY_URL';

    /**
     * @see APP_DEEPL_PROXY_ENABLED
     */
    public const DEEPL_PROXY_ENABLED = 'APP_DEEPL_PROXY_ENABLED';

    /**
     * @see APP_AUTH_SALT
     */
    public const AUTH_SALT = 'APP_AUTH_SALT';

    public const LDAP_HOST = 'APP_LDAP_HOST';
    public const LDAP_PORT = 'APP_LDAP_PORT';
    public const LDAP_USERNAME = 'APP_LDAP_USERNAME';
    public const LDAP_DN = 'APP_LDAP_DN';
    public const LDAP_PASSWORD = 'APP_LDAP_PASSWORD';
    public const LDAP_MEMBER_SUFFIX = 'APP_LDAP_MEMBER_SUFFIX';

    /**
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setBootDefine(string $name, $value) : self
    {
        boot_define($name, $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setConstant(string $name, $value) : self
    {
        define($name, $value);
        return $this;
    }
}
