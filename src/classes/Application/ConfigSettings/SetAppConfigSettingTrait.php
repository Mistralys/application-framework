<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\ConfigSettings\SetAppConfigSettingTrait
 */

declare(strict_types=1);

namespace Application\ConfigSettings;

use UI\AdminURLs\AdminURLInterface;

/**
 * Trait with setter methods for all application configuration settings.
 * Meant to be used in tandem with the {@see SetConfigSettingInterface}
 * interface.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see SetConfigSettingInterface
 */
trait SetAppConfigSettingTrait
{
    /**
     * @param bool $enabled
     * @return $this
     */
    public function setSimulateSession(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::SIMULATE_SESSION, $enabled);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setDemoMode(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DEMO_MODE, $enabled);
    }

    /**
     * Whether to enable the application log, which collects all
     * logging messages into memory during the requests.
     * When enabled, the log is added to exceptions in the error log.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setLoggingEnabled(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LOGGING_ENABLED, $enabled);
    }

    /**
     * @param string $instanceID
     * @return $this
     */
    public function setInstanceID(string $instanceID) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::INSTANCE_ID, $instanceID);
    }

    /**
     * Whether the database should be enabled for this request.
     * Turning it off means that the database will not be initialized,
     * and any attempts to run queries will fail with an exception.
     *
     * This is meant to be used for requests that do not require
     * a database connection, for performance reasons.
     *
     * @param bool $enabled
     * @return $this
     *
     * @see \Application::isDatabaseEnabled()
     */
    public function setDBEnabled(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_ENABLED, $enabled);
    }

    /**
     * @param string $theme
     * @return $this
     */
    public function setTheme(string $theme) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::THEME, $theme);
    }

    /**
     * Whether to display all queries of the current request in the
     * footer of the UI. Used for debugging purposes.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setShowQueries(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::SHOW_QUERIES, $enabled);
    }

    /**
     * Whether all database queries should be tracked by storing
     * them in memory, so the history can be accessed anytime.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setTrackQueries(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::TRACK_QUERIES, $enabled);
    }

    /**
     * Only used when using OAuth sessions: Sets the private
     * salt string used to encrypt data.
     *
     * @param string $salt
     * @return $this
     */
    public function setAuthSalt(string $salt) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::AUTH_SALT, $salt);
    }

    /**
     * Sets the mode in which the application should run for this request.
     *
     * @param string $runMode
     * @return $this
     *
     * @see \Application::RUN_MODE_SCRIPT
     * @see \Application::RUN_MODE_UI
     */
    public function setRunMode(string $runMode) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::RUN_MODE, $runMode);
    }

    /**
     * Whether to disable authentication in this request.
     *
     * WARNING: Use with caution.
     *
     * This effectively disables user authentication entirely,
     * and must be reserved to cases where this is absolutely
     * necessary. A good example is a publicly available API
     * endpoint.
     *
     * @param bool $noAuthentication
     * @return $this
     */
    public function setNoAuthentication(bool $noAuthentication) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::NO_AUTHENTICATION, $noAuthentication);
    }

    /**
     * The default delay, in seconds, to leave revisionable records
     * in deleted state before destroying them automatically.
     *
     * @param int $delay
     * @return $this
     */
    public function setAutomaticDeletionDelay(int $delay) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::AUTOMATIC_DELETION_DELAY, $delay);
    }

    /**
     * @param string $appSetID
     * @return $this
     */
    public function setAppSet(string $appSetID) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::APPSET, $appSetID);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setJavascriptMinified(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::JAVASCRIPT_MINIFIED, $enabled);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setDBName(string $name) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_NAME, $name);
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setDBHost(string $host) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_HOST, $host);
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setDBPort(int $port) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_PORT, $port);
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setDBUser(string $user) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_USER, $user);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setDBPassword(string $password) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_PASSWORD, $password);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setDBTestsName(string $name) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_TESTS_NAME, $name);
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setDBTestsHost(string $host) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_TESTS_HOST, $host);
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setDBTestsPort(int $port) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_TESTS_PORT, $port);
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setDBTestsUser(string $user) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_TESTS_USER, $user);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setDBTestsPassword(string $password) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DB_TESTS_PASSWORD, $password);
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className) : self
    {
        return $this->setConstant(BaseConfigRegistry::CLASS_NAME, $className);
    }

    /**
     * @param string $companyName
     * @return $this
     */
    public function setCompanyName(string $companyName) : self
    {
        return $this->setConstant(BaseConfigRegistry::COMPANY_NAME, $companyName);
    }

    /**
     * @param string $companyHomepage
     * @return $this
     */
    public function setCompanyHomepage(string $companyHomepage) : self
    {
        return $this->setConstant(BaseConfigRegistry::COMPANY_HOMEPAGE, $companyHomepage);
    }

    /**
     * @param string $dummyEmail
     * @return $this
     */
    public function setDummyEmail(string $dummyEmail) : self
    {
        return $this->setConstant(BaseConfigRegistry::DUMMY_EMAIL, $dummyEmail);
    }

    /**
     * @param string $systemEmail
     * @return $this
     */
    public function setSystemEmail(string $systemEmail) : self
    {
        return $this->setConstant(BaseConfigRegistry::SYSTEM_EMAIL, $systemEmail);
    }

    /**
     * @param string $systemName
     * @return $this
     */
    public function setSystemName(string $systemName) : self
    {
        return $this->setConstant(BaseConfigRegistry::SYSTEM_NAME, $systemName);
    }

    /**
     * Sets a comma-separated list of email addresses to send
     * system emails to.
     *
     * @param string $recipients
     * @return $this
     */
    public function setSystemEmailRecipients(string $recipients) : self
    {
        return $this->setConstant(BaseConfigRegistry::SYSTEM_EMAIL_RECIPIENTS, $recipients);
    }

    /**
     * @param string|AdminURLInterface $url
     * @return $this
     */
    public function setURL($url) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::URL, (string)$url);
    }

    /**
     * The URL to the Composer <code>vendor</code> folder. This is determined
     * automatically from the {@see BaseConfigRegistry::URL} setting, but can be
     * overridden here if the application setup is different.
     *
     * @param string|AdminURLInterface $url
     * @return $this
     */
    public function setVendorURL($url) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::VENDOR_URL, (string)$url);
    }

    /**
     * @param string[] $locales
     * @return $this
     */
    public function setContentLocales(array $locales) : self
    {
        return $this->setConstant(BaseConfigRegistry::CONTENT_LOCALES, implode(',', $locales));
    }

    /**
     * @param string[] $locales
     * @return $this
     */
    public function setUILocales(array $locales) : self
    {
        return $this->setConstant(BaseConfigRegistry::UI_LOCALES, implode(',', $locales));
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setRequestLogPassword(string $password) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::REQUEST_LOG_PASSWORD, $password);
    }

    /**
     * @param string|AdminURLInterface $url
     * @return $this
     */
    public function setInstallURL($url) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::INSTALL_URL, (string)$url);
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setDeeplAPIKey(string $key) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DEEPL_API_KEY, $key);
    }

    /**
     * @param string|AdminURLInterface $url
     * @return $this
     */
    public function setDeeplProxyURL($url) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DEEPL_PROXY_URL, (string)$url);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setDeeplProxyEnabled(bool $enabled) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::DEEPL_PROXY_ENABLED, $enabled);
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setCASHost(string $host) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::CAS_HOST, $host);
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setCASPort(int $port) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::CAS_PORT, $port);
    }

    /**
     * Sets the absolute URI to access the server.
     * @param string $uri
     * @return $this
     */
    public function setCASServerURI(string $uri) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::CAS_SERVER_URI, $uri);
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setCASLogoutURL(string $url) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::CAS_LOGOUT_URL, $url);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setCASName(string $name) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::CAS_NAME, $name);
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setLDAPHost(string $host) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LDAP_HOST, $host);
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setLDAPPort(int $port) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LDAP_PORT, $port);
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setLDAPUsername(string $username) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LDAP_USERNAME, $username);
    }

    /**
     * @param string $dn
     * @return $this
     */
    public function setLDAPDN(string $dn) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LDAP_DN, $dn);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setLDAPPassword(string $password) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LDAP_PASSWORD, $password);
    }

    /**
     * @param string $memberSuffix
     * @return $this
     */
    public function setLDAPMemberSuffix(string $memberSuffix) : self
    {
        return $this->setBootDefine(BaseConfigRegistry::LDAP_MEMBER_SUFFIX, $memberSuffix);
    }
}
