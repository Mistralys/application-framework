<?php
/**
 * @package LDAP
 * @subpackage Tests
 */

declare(strict_types=1);

namespace AppFrameworkTests\LDAP;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application_LDAP_Config;

/**
 * Tests for the LDAP authentication class.
 *
 * @package LDAP
 * @subpackage Tests
 */
final class ConfigTests extends ApplicationTestCase
{
    public function test_defaults() : void
    {
        $config = new Application_LDAP_Config(
            '127.0.0.1',
            null,
            'dc=mokapi,dc=io',
            'uid=awilliams,dc=mokapi,dc=io',
            'foo123'
        );

        $this->assertSame(389, $config->getPort(), 'Default port should be 389 when not specified.');
        $this->assertSame(3, $config->getProtocolVersion(), 'Default protocol version should be 3.');
        $this->assertTrue($config->isSSLEnabled(), 'Default SSL setting should be true.');
        $this->assertStringContainsString('ldaps://', $config->getURI(), 'URI should use ldaps:// protocol.');
    }
}
