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
        return boot_constant(BaseConfigRegistry::URL);
    }

    public static function getRootFolder() : string
    {
        return boot_constant(BaseConfigRegistry::ROOT);
    }

    public static function getInstallFolder() : string
    {
        return boot_constant(BaseConfigRegistry::INSTALL_FOLDER);
    }

    public static function getInstallURL() : string
    {
        return boot_constant(BaseConfigRegistry::INSTALL_URL);
    }

    public static function getVendorFolder() : string
    {
        return boot_constant(BaseConfigRegistry::VENDOR_PATH);
    }

    public static function getVendorURL() : string
    {
        return boot_constant(BaseConfigRegistry::VENDOR_URL);
    }

    public static function getClassName() : string
    {
        return boot_constant(BaseConfigRegistry::CLASS_NAME);
    }

    public static function getCompanyName() : string
    {
        return boot_constant(BaseConfigRegistry::COMPANY_NAME);
    }

    public static function getCompanyHomepage() : string
    {
        return boot_constant(BaseConfigRegistry::COMPANY_HOMEPAGE);
    }

    public static function getDummyEmail() : string
    {
        return boot_constant(BaseConfigRegistry::DUMMY_EMAIL);
    }

    public static function getSystemEmail() : string
    {
        return boot_constant(BaseConfigRegistry::SYSTEM_EMAIL);
    }

    public static function getSystemName() : string
    {
        return boot_constant(BaseConfigRegistry::SYSTEM_NAME);
    }

    public static function isSessionSimulated() : bool
    {
        return boot_constant(BaseConfigRegistry::SIMULATE_SESSION) === true;
    }

    public static function isJavascriptMinified() : bool
    {
        return boot_constant(BaseConfigRegistry::JAVASCRIPT_MINIFIED) === true;
    }

    public static function isDemoMode() : bool
    {
        return boot_constant(BaseConfigRegistry::DEMO_MODE) === true;
    }

    public static function isLoggingEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::LOGGING_ENABLED) === true;
    }

    public static function isDeveloperMode() : bool
    {
        return boot_constant(BaseConfigRegistry::DEVELOPER_MODE) === true;
    }

    public static function isQueryTrackingEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::TRACK_QUERIES) === true;
    }

    public static function isShowQueriesEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::SHOW_QUERIES) === true;
    }

    public static function isAuthenticationEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::NO_AUTHENTICATION) !== true;
    }

    public static function isDBEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::DB_ENABLED) === true;
    }

    public static function getEnvironmentID() : string
    {
        return boot_constant(BaseConfigRegistry::ENVIRONMENT);
    }

    public static function getRunMode() : string
    {
        return boot_constant(BaseConfigRegistry::RUN_MODE);
    }

    public static function getTheme() : string
    {
        return boot_constant(BaseConfigRegistry::THEME);
    }

    // region: LDAP settings

    public static function getLDAPHost() : string
    {
        return (string)boot_constant(BaseConfigRegistry::LDAP_HOST);
    }

    public static function getLDAPPort() : ?int
    {
        $value = boot_constant(BaseConfigRegistry::LDAP_PORT);
        if(is_int($value) || is_string($value)) {
            return (int)$value;
        }

        return null;
    }

    public static function getLDAPDN() : string
    {
        return (string)boot_constant(BaseConfigRegistry::LDAP_DN);
    }

    public static function getLDAPUsername() : string
    {
        return (string)boot_constant(BaseConfigRegistry::LDAP_USERNAME);
    }

    public static function getLDAPPassword() : string
    {
        return (string)boot_constant(BaseConfigRegistry::LDAP_PASSWORD);
    }

    public static function getLDAPMemberSuffix() : string
    {
        return (string)boot_constant(BaseConfigRegistry::LDAP_MEMBER_SUFFIX);
    }

    public static function isLDAPSSLEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::LDAP_SSL_ENABLED) === true;
    }

    // endregion

    /**
     * The maximum size, in bytes, of the decoded content a
     * {@see \Application\API\Parameters\Type\Base64FileParameter} accepts
     * by default, unless overridden with `setMaxSize()`.
     *
     * @return int
     */
    public static function getAPIMaxFileContentSize() : int
    {
        return (int)boot_constant(BaseConfigRegistry::API_MAX_FILE_CONTENT_SIZE);
    }

    /**
     * The fixed overhead, in bytes, added on top of the base64-inflated
     * file content limit when deriving the default API request body size
     * ceiling (see {@see self::deriveAPIMaxRequestBodySize()}). Accounts for
     * the rest of the JSON payload surrounding the encoded file content
     * (metadata fields, JSON structure/escaping).
     */
    public const API_REQUEST_BODY_OVERHEAD = 1048576;

    /**
     * Derives the maximum accepted API request body size, in bytes.
     *
     * Pure helper (no boot-constant access), so it can be exercised directly
     * in unit tests with arbitrary inputs — boot settings are fixed once the
     * application has booted and cannot be lowered from a test.
     *
     * @param int $configured The explicitly configured ceiling
     *        ({@see \Application\ConfigSettings\BaseConfigRegistry::API_MAX_REQUEST_BODY_SIZE}).
     *        A value of `0` or less means "derive the default instead".
     * @param int $fileContentLimit The configured file-content size limit
     *        ({@see \Application\ConfigSettings\BaseConfigRegistry::API_MAX_FILE_CONTENT_SIZE}),
     *        used as the basis for the derived default.
     * @return int
     */
    public static function deriveAPIMaxRequestBodySize(int $configured, int $fileContentLimit) : int
    {
        if($configured > 0) {
            return $configured;
        }

        return intdiv($fileContentLimit * 4, 3) + self::API_REQUEST_BODY_OVERHEAD;
    }

    /**
     * The maximum size, in bytes, an incoming API request body may have.
     * Uses the explicitly configured ceiling
     * ({@see \Application\ConfigSettings\BaseConfigRegistry::API_MAX_REQUEST_BODY_SIZE})
     * when set, otherwise derives a default from the configured file-content
     * size limit (see {@see self::deriveAPIMaxRequestBodySize()}).
     *
     * @return int
     */
    public static function getAPIMaxRequestBodySize() : int
    {
        return self::deriveAPIMaxRequestBodySize(
            (int)boot_constant(BaseConfigRegistry::API_MAX_REQUEST_BODY_SIZE),
            self::getAPIMaxFileContentSize()
        );
    }
}
