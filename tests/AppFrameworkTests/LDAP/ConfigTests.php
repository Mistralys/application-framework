<?php
/**
 * @package LDAP
 * @subpackage Tests
 */

declare(strict_types=1);

namespace AppFrameworkTests\LDAP;

use AppFrameworkTestClasses\LDAP\LDAPTestCase;
use Application;
use Application\ConfigSettings\AppConfig;
use Application\LDAP\LDAPException;
use Application_LDAP_Config;

/**
 * Tests for the LDAP authentication class.
 *
 * @package LDAP
 * @subpackage Tests
 */
final class ConfigTests extends LDAPTestCase
{
    public function test_defaultsSSL() : void
    {
        $config = new Application_LDAP_Config(
            '127.0.0.1',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->assertSame(636, $config->getPort(), 'Default port should be 389 when not specified.');
        $this->assertSame(3, $config->getProtocolVersion(), 'Default protocol version should be 3.');
        $this->assertTrue($config->isSSLEnabled(), 'Default SSL setting should be true.');
        $this->assertEquals('ldaps://127.0.0.1:636', $config->getURI());
        $this->assertEquals('ldaps://127.0.0.1', $config->getHostURI());
    }

    public function test_defaultsNoSSL() : void
    {
        $config = new Application_LDAP_Config(
            '127.0.0.1',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $config->setSSLEnabled(false);

        $this->assertSame(389, $config->getPort(), 'Default port should be 389 when not specified.');
        $this->assertSame(3, $config->getProtocolVersion(), 'Default protocol version should be 3.');
        $this->assertFalse($config->isSSLEnabled(), 'Default SSL setting should be false.');
        $this->assertEquals('ldap://127.0.0.1:389', $config->getURI());
        $this->assertEquals('ldap://127.0.0.1', $config->getHostURI());
    }

    public function test_normalizeHost() : void
    {
        $config = new Application_LDAP_Config(
            'ldaps://ldap.example.com/',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->assertSame('ldap.example.com', $config->getHost());
    }

    public function test_detectSSLFromHost() : void
    {
        $config = new Application_LDAP_Config(
            'ldap://ldap.example.com/',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->assertFalse($config->isSSLEnabled(), 'SSL should be disabled for ldap:// scheme.');
    }

    public function test_invalidHost() : void
    {
        $this->expectExceptionCode(LDAPException::ERROR_INVALID_HOST);

        new Application_LDAP_Config(
            '//////',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );
    }

    public function test_validHost() : void
    {
        new Application_LDAP_Config(
            'ldap.host.org',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->addToAssertionCount(1);

        new Application_LDAP_Config(
            'ldap://ldap.host.org',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->addToAssertionCount(1);

        new Application_LDAP_Config(
            'ldaps://ldap.host.org',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->addToAssertionCount(1);
    }

    /**
     * The application configuration settings must match
     * the LDAP integration tests configuration, as they
     * use the same settings.
     */
    public function test_applicationConfigSettings() : void
    {
        $this->assertSame(self::LDAP_HOST, AppConfig::getLDAPHost());
        $this->assertSame(self::LDAP_PORT, AppConfig::getLDAPPort());
        $this->assertSame(self::LDAP_DN, AppConfig::getLDAPDN());
        $this->assertSame(self::LDAP_USERNAME, AppConfig::getLDAPUsername());
        $this->assertSame(self::LDAP_PASSWORD, AppConfig::getLDAPPassword());
        $this->assertSame(self::LDAP_MEMBER_SUFFIX, AppConfig::getLDAPMemberSuffix());
        $this->assertSame(self::LDAP_SSL_ENABLED, AppConfig::isLDAPSSLEnabled());

        $config = Application::createLDAP()->getConfig();

        $this->assertSame(self::LDAP_HOST, $config->getHost());
        $this->assertSame(self::LDAP_PORT, $config->getPort());
        $this->assertSame(self::LDAP_DN, $config->getDN());
        $this->assertSame(self::LDAP_USERNAME, $config->getUsername());
        $this->assertSame(self::LDAP_PASSWORD, $config->getPassword());
        $this->assertSame(self::LDAP_MEMBER_SUFFIX, $config->getMemberSuffix());
        $this->assertSame(self::LDAP_SSL_ENABLED, $config->isSSLEnabled());
    }
}
