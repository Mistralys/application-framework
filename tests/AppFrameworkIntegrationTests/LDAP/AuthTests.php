<?php
/**
 * @package LDAP
 * @subpackage Integration Tests
 */

declare(strict_types=1);

namespace AppFrameworkIntegrationTests\LDAP;

use AppFrameworkTestClasses\LDAP\LDAPTestCase;
use Application;
use Application_LDAP;
use Application_LDAP_Config;

/**
 * Integration tests for the LDAP authentication class.
 *
 * ## Usage
 *
 * To set this up, please refer to the following file:
 * {@see /tests/assets/ldap-mokapi/_readme.md}`.
 *
 * @package LDAP
 * @subpackage Integration Tests
 */
final class AuthTests extends LDAPTestCase
{
    // region: _Tests

    /**
     * Instantiating the LDAP class does an `ldap_connect()`.
     * This test checks that the configuration is valid.
     */
    public function test_configure() : void
    {
        new Application_LDAP($this->createLDAPConfig());

        $this->addToAssertionCount(1);
    }

    public function test_bind() : void
    {
        $ldap = new Application_LDAP($this->createLDAPConfig());

        // Trigger calling bind() by fetching rights.
        $ldap->getRights('awilliams');

        $this->addToAssertionCount(1);
    }

    public function test_getUserRights() : void
    {
        $ldap = new Application_LDAP($this->createLDAPConfig());

        $rights = $ldap->getRights('awilliams');

        $this->assertSame(array('EditProducts', 'ViewProducts'), $rights);
    }

    public function test_getAdminRights() : void
    {
        $ldap = new Application_LDAP($this->createLDAPConfig());

        $rights = $ldap->getRights('csmith');

        $this->assertSame(array('DeleteProducts', 'EditProducts', 'ViewProducts'), $rights);
    }

    /**
     * The test application uses the same LDAP configuration as
     * the LDAP test cases, so it must give the same results as
     * the internal configuration tests.
     */
    public function test_applicationConfigSettings() : void
    {
        $rights = Application::createLDAP()->getRights('awilliams');

        $this->assertSame(array('EditProducts', 'ViewProducts'), $rights);
    }

    // endregion

    // region: Support methods

    protected function createLDAPConfig(bool $debug=false) : Application_LDAP_Config
    {
        return (new Application_LDAP_Config(
            self::LDAP_HOST,
            self::LDAP_PORT,
            self::LDAP_DN,
            self::LDAP_USERNAME,
            self::LDAP_PASSWORD
        ))
            ->setDebug($debug)
            ->setSSLEnabled(self::LDAP_SSL_ENABLED)
            ->setMemberSuffix(self::LDAP_MEMBER_SUFFIX)
            ->setProtocolVersion(3);
    }

    // endregion
}
