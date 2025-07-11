<?php
/**
 * @package LDAP
 * @subpackage Integration Tests
 */

declare(strict_types=1);

namespace AppFrameworkIntegrationTests\LDAP;

use AppFrameworkTestClasses\ApplicationTestCase;
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
final class AuthTests extends ApplicationTestCase
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

    // endregion

    // region: Support methods

    protected function createLDAPConfig(bool $debug=false) : Application_LDAP_Config
    {
        return (new Application_LDAP_Config(
            '127.0.0.1',
            9689, // Custom port for testing
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        ))
            ->setSSLEnabled(false)
            ->setDebug($debug)
            ->setMemberSuffix(',dc=mokapi,dc=io')
            ->setProtocolVersion(3);
    }

    // endregion
}
